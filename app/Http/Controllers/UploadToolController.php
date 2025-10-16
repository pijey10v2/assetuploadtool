<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use PDO;

class UploadToolController extends Controller
{
    public function index()
    {
        return view('uploadtool');
    }

    public function store(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $request->validate([
            'bimfile' => 'required|file|mimes:bim,sqlite,sqlite3,db|max:51200',
            'rawfile' => 'required|file|mimes:xlsx,xls,csv|max:51200',
            'import_batch_no' => 'required|string',
            'data_id' => 'required|string',
        ]);

        // Save uploaded files
        $bimPath = $request->file('bimfile')->store('uploads');
        $rawPath = $request->file('rawfile')->store('uploads');

        $bimFullPath = storage_path('app/' . $bimPath);
        $rawFullPath = storage_path('app/' . $rawPath);

        // --- Step 1: Read SQLite BIM Columns ---
        $pdo = new \PDO("sqlite:" . $bimFullPath);
        $sql = "
            SELECT t.ElementId, t.ps2, t.ps3
            FROM bis_ElementMultiAspect t
            WHERE t.ps3 = 'Element'
            AND CAST(t.ps2 AS REAL) IS NOT NULL;
        ";
        $bimResults = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        // --- Step 2: Read Excel Columns ---
        $excel = Excel::toCollection(null, $request->file('rawfile'));
        $firstSheet = $excel->first();
        $firstRow = $firstSheet?->first();
        $excelColumns = $firstRow ? array_keys($firstRow->toArray()) : [];

        // --- Step 3: Call AMS APIs ---
        $amsUrl = 'https://ams.reveronconsulting.com/JavaBridge/asset/index.php';

        $dbResponse = Http::asForm()->post($amsUrl, [
            'import_batch_no' => $request->import_batch_no,
            'data_id' => $request->data_id,
            'asset_table_name' => 'app_fd_inv_pavement',
            'mode' => 'get_table_columns'
        ])->json();

        $excelResponse = Http::asForm()->post($amsUrl, [
            'import_batch_no' => $request->import_batch_no,
            'data_id' => $request->data_id,
            'asset_table_name' => 'app_fd_inv_pavement',
            'rawfile_mapping' => json_encode([$bimResults[0] ?? []]),
            'mode' => 'get_excel_columns'
        ])->json();

        return response()->json([
            'message' => 'Files processed successfully.',
            'bim_count' => count($bimResults),
            'excel_columns' => $excelColumns,
            'db_columns' => $dbResponse['columns'] ?? [],
            'raw_columns' => $excelResponse['columns'] ?? [],
        ]);
    }


    public function store1(Request $request)
    {
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('uploads');
            return back()->with('success', "File uploaded to: $path");
        }
        return back()->with('error', 'No file selected');
    }
}
