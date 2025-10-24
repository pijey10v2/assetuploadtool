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
            'rawfile' => 'required|file|mimes:xlsx,xls,csv|max:51200',
            'import_batch_no' => 'required|string',
            'data_id' => 'required|string',
        ]);

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

        return response()->json([
            'message' => 'Files processed successfully.',
            'bim_count' => count($bimResults),
            'bim_results' => $bimData, 
            'db_columns' => $dbResponse['columns'] ?? [],
            'raw_columns' => $excelResponse['columns'] ?? [],
            'rawfile_mapping' => $rawfile_mapping,
            'rawfile_path' => $rawPath, // Make sure this exists
            'raw_filename' => $rawFileName
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

}
