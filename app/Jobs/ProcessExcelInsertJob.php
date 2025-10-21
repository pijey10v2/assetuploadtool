<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ProcessExcelInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rawPath;
    protected $mappings;
    protected $importBatchNo;
    protected $dataId;
    protected $jobId;
    protected $assetTableName;

    /**
     * Create a new job instance.
     */
    public function __construct($jobId, $rawPath, $mappings, $importBatchNo, $dataId, $assetTableName)
    {
        $this->rawPath = $rawPath;
        $this->mappings = $mappings;
        $this->importBatchNo = $importBatchNo;
        $this->dataId = $dataId;
        $this->jobId = $jobId;
        $this->assetTableName = $assetTableName;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $rawFullPath = storage_path('app/' . $this->rawPath);

        if (!Storage::exists($this->rawPath)) {
            Cache::put("upload_progress_{$this->jobId}", [
                'status' => 'error',
                'message' => 'Raw file not found.'
            ], now()->addMinutes(10));
            return;
        }

        $excelData = Excel::toCollection(null, $rawFullPath)->first();
        $headerRow = $excelData->first()->toArray();
        $dataRows = $excelData->skip(1)->map(fn($r) => $r->toArray())->values();

        $totalRows = $dataRows->count();
        $processed = 0;
        $inserted = 0;

        $apiUrl = env('JOGET_API_URL');

        foreach ($dataRows->chunk(100) as $chunk) {
            foreach ($chunk as $row) {
                $mapped = [];
                foreach ($this->mappings as $map) {
                    $dbCol = array_keys($map)[0];
                    $excelCol = $map[$dbCol];

                    $colIndex = array_search($excelCol, array_values($headerRow));
                    $mapped[$dbCol] = $colIndex !== false ? $row[$colIndex] ?? null : null;
                }

                $response = Http::asForm()->post($apiUrl, [
                    'mode' => 'insert_asset_data',
                    'import_batch_no' => $this->importBatchNo,
                    'data_id' => $this->dataId,
                    'asset_table_name' => $this->assetTableName,
                    'row_data' => json_encode($mapped),
                ]);

                if ($response->successful()) {
                    $inserted++;
                }

                $processed++;

                Cache::put("upload_progress_{$this->jobId}", [
                    'status' => 'processing',
                    'processed' => $processed,
                    'total' => $totalRows,
                    'inserted' => $inserted,
                    'progress' => round(($processed / $totalRows) * 100)
                ], now()->addMinutes(10));
            }

            // Slight delay between chunks (optional)
            usleep(300000); // 0.3 sec
        }

        Cache::put("upload_progress_{$this->jobId}", [
            'status' => 'done',
            'inserted' => $inserted,
            'total' => $totalRows,
            'progress' => 100
        ], now()->addMinutes(10));
    }
}
