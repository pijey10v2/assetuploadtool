<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use PDO;
use Illuminate\Support\Facades\Storage;
use App\Models\ProjectLayer;
use App\Jobs\ProcessExcelInsertJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\AssetMapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

ini_set('memory_limit', '1024M'); // Increase memory
set_time_limit(0); // Disable script timeout

class UploadToolController extends Controller
{
    public function index()
    {
        // Fetch Data_ID and Layer_Name pairs from SQL Server
        $layers = ProjectLayer::select('Data_ID', 'Layer_Name')
            ->whereNotNull('Data_ID')
            ->orderBy('Layer_Name', 'asc')
            ->distinct()
            ->get();

         // Get all .bim files inside storage/app/bimfiles
        $bimFiles = collect(Storage::files('bimfiles'))
            ->filter(fn($file) => str_ends_with(strtolower($file), '.bim'))
            ->map(fn($file) => basename($file))
            ->values();

        return view('uploadtool.index', compact('bimFiles', 'layers'));
    }

    public function store(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $request->validate([
            // 'bimfile' => 'required|file|mimes:bim,sqlite,sqlite3,db|max:51200',
            'bimfile' => 'required|string',
            'rawfile' => 'required|file|mimes:xlsx,xls|max:51200',
            'import_batch_no' => 'nullable|string',
            'data_id' => 'required|string',
        ]);

        // Generate batch number if missing
        if (empty($request->import_batch_no)) {
            $autoBatchNo = $this->makeImportBatchNo($request->asset_table_name);
            $request->merge(['import_batch_no' => $autoBatchNo]);
        }

        $rawOriginalName = pathinfo($request->file('rawfile')->getClientOriginalName(), PATHINFO_FILENAME);
        $rawExtension = $request->file('rawfile')->getClientOriginalExtension();
        // Add readable timestamp suffix: -YYYY-MM-DD HH-MM-SS
        $timestamp = now()->format('Y-m-d H-i-s');

        // Save uploaded files
        // $bimPath = $request->file('bimfile')->store('uploads');
        // $rawPath = $request->file('rawfile')->store('uploads');
        $rawFileName = "{$rawOriginalName}-{$timestamp}.{$rawExtension}";
        $rawPath = $request->file('rawfile')->storeAs('uploads', $rawFileName);

        // Use selected BIM file from storage/app/bimfiles
        //$bimFullPath = storage_path('app/' . $bimPath);
        $bimFullPath = storage_path('app/bimfiles/' . $request->bimfile);
        $rawFullPath = storage_path('app/' . $rawPath);

        // Step 1: Read SQLite BIM Columns
        $pdo = new \PDO("sqlite:" . $bimFullPath);
        $sql = "
            SELECT t.ElementId, t.ps2, t.ps3
            FROM bis_ElementMultiAspect t
            WHERE t.ps3 = 'Element'
            AND CAST(t.ps2 AS REAL) IS NOT NULL;
        ";
        $bimResults = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $bimData = collect($bimResults)
        ->map(fn($row) => [
            'ElementId' => $row['ElementId'],
            'ps2' => $row['ps2'],
            //'ps3' => $row['ps3'],
        ]);
        //->take(20); // Limit to 20 rows

        // Step 2: Read Excel file (headers + first data row)
        $excel = \Maatwebsite\Excel\Facades\Excel::toCollection(null, $request->file('rawfile'));
        $firstSheet = $excel->first();

        $headers = [];
        $dataRows = [];

        if ($firstSheet && $firstSheet->count() > 0) {
            $headers = $firstSheet->first()->toArray(); // first row = headers

            // get next rows as data
            $dataRows = $firstSheet->skip(1)->map(fn($row) => $row->toArray())->filter()->values();
        }

        // Combine header + first data row for mapping
        $rawfile_mapping = [];
        if (!empty($headers) && $dataRows->isNotEmpty()) {
            $firstData = $dataRows->first();
            $rawfile_mapping[] = array_combine($headers, $firstData);
        }

        // Step 3: Call APIs
        $apiUrl = env('JOGET_API_URL');

        // Get database table columns
        $dbResponse = Http::asForm()->post($apiUrl, [
            'import_batch_no' => $request->import_batch_no,
            'data_id' => $request->data_id,
            'asset_table_name' => $request->asset_table_name,
            'mode' => 'get_table_columns'
        ])->json();

        // Get excel columns (mapping)
        $excelResponse = Http::asForm()->post($apiUrl, [
            'import_batch_no' => $request->import_batch_no,
            'data_id' => $request->data_id,
            'asset_table_name' => $request->asset_table_name,
            'rawfile_mapping' => json_encode($rawfile_mapping ?? []),
            'mode' => 'get_excel_columns'
        ])->json();

        // Save recent mapping (by createdBy + table name)
        $recentMapping = AssetMapping::where('createdBy', Auth::user()->email)
        ->where('asset_table_name', $request->asset_table_name)
        ->first();

        return response()->json([
            'message' => 'Files processed successfully.',
            'bim_count' => count($bimResults),
            'bim_results' => $bimData, 
            'db_columns' => $dbResponse['columns'] ?? [],
            'raw_columns' => $excelResponse['columns'] ?? [],
            'rawfile_mapping' => $rawfile_mapping,
            'rawfile_path' => $rawPath, // Make sure this exists
            'raw_filename' => $rawFileName,
            'recent_mapping' => $recentMapping ? $recentMapping->mappings : null, // include previous mapping
            'import_batch_no' => $request->import_batch_no,
        ]);
    }

    public function executeUpdate(Request $request)
    {
        $request->validate([
            'mappings' => 'required|array',
            'rawfile_path' => 'required|string',
            'import_batch_no' => 'required|string',
            'data_id' => 'required|string',
            'asset_table_name' => 'required|string',
        ]);

        // Generate unique job ID
        $jobId = uniqid('upload_', true);

        // Store initial progress
        Cache::put("upload_progress_{$jobId}", [
            'status' => 'starting',
            'progress' => 0
        ], now()->addMinutes(10));

        // Log dispatch
        Log::info("Dispatching job: {$jobId}");

        // Get current user
        $user = Auth::user();

        // Save or update recent mapping (by createdBy + table name)
        AssetMapping::updateOrCreate(
            [
                'createdBy' => $user->email,
                'asset_table_name' => $request->asset_table_name,
            ],
            [
                'createdByName' => $user->name,
                'mappings' => $request->mappings,
            ]
        );

        // Retrieve project-related metadata from SQL Server
        $projectData = null;

        try {
            // Query Project_Layers table to get Project_ID
            $layer = DB::table('Project_Layers')
                ->select('Data_ID', 'Project_ID')
                ->where('Data_ID', $request->data_id)
                ->first();

            if ($layer) {
                // Use the retrieved Project_ID to get details from the projects table
                $project = DB::table('projects')
                ->select('project_id', 'project_id_number', 'parent_project_id_number', 'project_owner')
                ->where('project_id_number', $layer->Project_ID)
                ->first();

                if ($project) {

                    // Get parent project info (optional if exists)
                    $parent = DB::table('projects')
                        ->select('project_id_number', 'project_id')
                        ->where('project_id_number', $project->parent_project_id_number)
                        ->first();

                    // Build derived mapping
                    $projectData = [
                        'c_package_id'    => $project->project_id ?? null,
                        'c_package_uuid'  => ($project->project_id_number ?? '') . '_' . ($project->project_id ?? '') . '_' . ($project->project_id_number ?? ''),
                        'c_project_id'    => $parent->project_id ?? null,
                        'c_project_owner' => $project->project_owner ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching project data: ' . $e->getMessage());
            $projectData = null;
        }

        // Dispatch background job
        ProcessExcelInsertJob::dispatch(
            $jobId, 
            $request->rawfile_path, 
            $request->mappings, 
            $request->import_batch_no, 
            $request->data_id, 
            $request->asset_table_name, 
            $request->bim_results,
            $user->email,     // createdBy
            $user->name       // createdByName
            , $projectData
        );

        return response()->json([
            'message' => 'Data update started.',
            'job_id' => $jobId,
            'rawfile_path' => $request->rawfile_path,
            'asset_table_name' => $request->asset_table_name,
            'import_batch_no' => $request->import_batch_no,
            'data_id' => $request->data_id,
            'bim_results' => $request->bim_results,
            'mappings' => $request->mappings,
            'createdBy' => $user->email,
            'createdByName' => $user->name,
            'project_data' => $projectData,
        ]);
    }

    public function getProgress(Request $request)
    {
        $jobId = $request->query('job_id');
        $progress = Cache::get("upload_progress_{$jobId}", [
            'status' => 'unknown',
            'progress' => 0
        ]);

        return response()->json($progress);
    }

    private function makeImportBatchNo($assetTableName)
    {
        $counter = \DB::table('asset_batch_counters')
            ->where('asset_table_name', $assetTableName)
            ->lockForUpdate()
            ->first();

        if ($counter) {
            $next = $counter->counter + 1;
            \DB::table('asset_batch_counters')
                ->where('asset_table_name', $assetTableName)
                ->update(['counter' => $next]);
        } else {
            $next = 1;
            \DB::table('asset_batch_counters')->insert([
                'asset_table_name' => $assetTableName,
                'counter' => $next,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $batchNo = sprintf('%s-%s-%04d', $assetTableName, now()->format('Ymd'), $next);

        return $batchNo; // return string (e.g. app_fd_inv_pavement-20251104-0001)
    }

}
