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
use Log;

class ProcessExcelInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rawPath;
    protected $mappings;
    protected $importBatchNo;
    protected $dataId;
    protected $jobId;
    protected $assetTableName;
    protected $bimResults;
    protected $createdBy;
    protected $createdByName;
    protected $projectData;

    /**
     * Create a new job instance.
     */
    public function __construct($jobId, $rawPath, $mappings, $importBatchNo, $dataId, $assetTableName, $bimResults, $createdBy, $createdByName, $projectData)
    {
        $this->rawPath = $rawPath;
        $this->mappings = $mappings;
        $this->importBatchNo = $importBatchNo;
        $this->dataId = $dataId;
        $this->jobId = $jobId;
        $this->assetTableName = $assetTableName;
        $this->bimResults = $bimResults;
        $this->createdBy = $createdBy;
        $this->createdByName = $createdByName;
        $this->projectData = $projectData;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info("Job started: {$this->jobId}");

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

                // Append project data (if available)
                if (!empty($this->projectData)) {
                    $mapped = array_merge($mapped, [
                        'c_package_id'    => $this->projectData['c_package_id'] ?? null,
                        'c_package_uuid'  => $this->projectData['c_package_uuid'] ?? null,
                        'c_project_id'    => $this->projectData['c_project_id'] ?? null,
                        'c_project_owner' => $this->projectData['c_project_owner'] ?? null,
                    ]);
                }

                $response = Http::asForm()->post($apiUrl, [
                    'mode' => 'insert_asset_data',
                    'import_batch_no' => $this->importBatchNo,
                    'data_id' => $this->dataId,
                    'asset_table_name' => $this->assetTableName,
                    'row_data' => json_encode($mapped),
                    'bim_results' => json_encode($this->bimResults),
                    'createdBy' => $this->createdBy ?? 'system@localhost',           // Email
                    'createdByName' => $this->createdByName ?? 'System Job',   // Name
                ]);

                if ($response->successful()) {
                    $inserted++;
                }

                $processed++;

                // Update cache every few rows
                if ($processed % 10 === 0 || $processed === $totalRows) {

                    $progressPercent = $totalRows > 0 ? round(($processed / $totalRows) * 100, 2) : 0;

                     \Log::info("Updating progress: {$processed}/{$totalRows} ({$progressPercent}%)");

                    Cache::put("upload_progress_{$this->jobId}", [
                        'status' => 'processing',
                        'processed' => $processed,
                        'total' => $totalRows,
                        'inserted' => $inserted,
                        'progress' => $progressPercent
                    ], now()->addMinutes(10));
                }
            }

            // Slight delay between chunks (optional)
            usleep(250000); // 0.25 sec
        }

        Cache::put("upload_progress_{$this->jobId}", [
            'status' => 'done',
            'inserted' => $inserted,
            'total' => $totalRows,
            'progress' => 100
        ], now()->addMinutes(10));

        Log::info("Job completed: {$this->jobId}");
    }
    
}
