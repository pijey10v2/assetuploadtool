<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Log;

class ProcessExcelChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    protected $jobId;
    protected $rows;
    protected $headerRow;
    protected $mappings;
    protected $importBatchNo;
    protected $dataId;
    protected $assetTableName;
    protected $bimResults;
    protected $createdBy;
    protected $createdByName;
    protected $projectData;

    public function __construct(
        $jobId,
        $rows,
        $headerRow,
        $mappings,
        $importBatchNo,
        $dataId,
        $assetTableName,
        $bimResults,
        $createdBy,
        $createdByName,
        $projectData
    ) {
        $this->jobId = $jobId;
        $this->rows = $rows;
        $this->headerRow = $headerRow;
        $this->mappings = $mappings;
        $this->importBatchNo = $importBatchNo;
        $this->dataId = $dataId;
        $this->assetTableName = $assetTableName;
        $this->bimResults = $bimResults;
        $this->createdBy = $createdBy;
        $this->createdByName = $createdByName;
        $this->projectData = $projectData;
    }

    public function handle()
    {
        $apiUrl = env('JOGET_API_URL');

        // Precompute column indexes
        $columnIndexMap = [];
        foreach ($this->mappings as $map) {
            $dbCol = array_key_first($map);
            $excelCol = $map[$dbCol];
            $columnIndexMap[$dbCol] = array_search($excelCol, $this->headerRow);
        }

        $payload = [];
        $validRowCount = 0;

        foreach ($this->rows as $row) {

            $mapped = [];

            // Map columns
            foreach ($columnIndexMap as $dbCol => $index) {
                $mapped[$dbCol] = $index !== false
                    ? trim($row[$index] ?? '')
                    : null;
            }

            // Check for required fields
            $requiredFields = [
                'c_section',
                'c_division',
            ];

            $hasEmptyRequiredField = false;

            foreach ($requiredFields as $field) {
                if (
                    !isset($mapped[$field]) ||
                    $mapped[$field] === null ||
                    trim($mapped[$field]) === '' ||
                    trim($mapped[$field]) === 'NULL'
                ) {
                    $hasEmptyRequiredField = true;
                    break;
                }
            }

            if ($hasEmptyRequiredField) {
                continue; // Skip row
            }

            // bim match filter
            $modelElement = trim($mapped['c_model_element'] ?? '');

            $bimMatch = collect($this->bimResults)
                ->firstWhere('ps2', $modelElement);

            if (!$bimMatch) {
                continue; // Skip row
            }

            $mapped['c_element_id'] = $bimMatch['ElementId'] ?? null;

            // project data
            if (!empty($this->projectData)) {
                $mapped += [
                    'c_package_id'    => $this->projectData['c_package_id'] ?? null,
                    'c_package_uuid'  => $this->projectData['c_package_uuid'] ?? null,
                    'c_project_id'    => $this->projectData['c_project_id'] ?? null,
                    'c_project_owner' => $this->projectData['c_project_owner'] ?? null,
                ];
            }

            $payload[] = $mapped;
            $validRowCount++;
        }


        // ***content-type SHOULD BE multipart/form-data***
        // content-type: multipart/form-data is ONLY used when you call attach() 
        $response = Http::retry(0, 0)
        ->connectTimeout(30)
        ->timeout(180)
        ->attach('mode', 'bulk_insert_asset_data')
        ->attach('import_batch_no', $this->importBatchNo)
        ->attach('data_id', $this->dataId)
        ->attach('asset_table_name', $this->assetTableName)
        ->attach('row_data', json_encode($payload))
        ->attach('bim_results', json_encode($this->bimResults))
        ->attach('createdBy', $this->createdBy)
        ->attach('createdByName', $this->createdByName)
        ->post($apiUrl);

        Log::info('Joget API response', [
            'job_id' => $this->jobId,
            'status' => $response->status(),
        ]);

        $inserted = $response->successful() ? count($payload) : 0;

        Cache::lock("upload_progress_lock_{$this->jobId}", 10)->block(5, function () use ($validRowCount) {

            $progress = Cache::get("upload_progress_{$this->jobId}");

            // Add ONLY valid rows
            $progress['processed'] += $validRowCount;
            $progress['inserted'] += $validRowCount;

            // Dynamically compute TOTAL valid rows
            $progress['total'] += $validRowCount;

            $progress['completed_chunks']++;

            // Progress %
            $progress['progress'] = $progress['total'] > 0
                ? round(($progress['processed'] / $progress['total']) * 100, 2)
                : 0;

            if ($progress['completed_chunks'] >= $progress['total_chunks']) {
                $progress['status'] = 'done';
                $progress['progress'] = 100;
            }

            // BIM count = valid matched rows
            $progress['bim_count'] = $progress['inserted'];

            Cache::put("upload_progress_{$this->jobId}", $progress, 600);
        });


        Log::info("Chunk completed: {$this->jobId}, rows=" . count($this->rows));
    }
}
