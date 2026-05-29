<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Log;

class ProcessExcelInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900;

    protected $jobId;
    protected $rawPath;
    protected $mappings;
    protected $importBatchNo;
    protected $dataId;
    protected $assetTableName;
    protected $bimResults;
    protected $createdBy;
    protected $createdByName;
    protected $projectData;
    protected $type;

    public function __construct(
        $jobId,
        $rawPath,
        $mappings,
        $importBatchNo,
        $dataId,
        $assetTableName,
        $bimResults,
        $createdBy,
        $createdByName,
        $projectData,
        $type
    ) {
        $this->jobId = $jobId;
        $this->rawPath = $rawPath;
        $this->mappings = $mappings;
        $this->importBatchNo = $importBatchNo;
        $this->dataId = $dataId;
        $this->assetTableName = $assetTableName;
        $this->bimResults = $bimResults;
        $this->createdBy = $createdBy;
        $this->createdByName = $createdByName;
        $this->projectData = $projectData;
        $this->type = $type;
    }

    public function handle()
    {
        $apiUrl = env('JOGET_API_URL');

        Log::info('API URL', [
            'url' => $apiUrl
        ]);
        
        Log::info("Parent import job started: {$this->jobId}");

        if (!Storage::exists($this->rawPath)) {
            Cache::put("upload_progress_{$this->jobId}", [
                'status' => 'error',
                'message' => 'Raw file not found'
            ], 600);
            return;
        }

        $excel = Excel::toCollection(null, storage_path('app/' . $this->rawPath))->first();
        $headerRow = $excel->first()->toArray();
        $dataRows = $excel->skip(1)->map(fn ($r) => $r->toArray());

        $filteredRows = $dataRows->filter(function ($row) {

            $row = $row; // already array

            // Required fields indexes 
            $sectionIndex = array_search('Section (SEC)', $this->mappings_flat ?? []);
            $divisionIndex = array_search('Division (DIV)', $this->mappings_flat ?? []);

            $section = trim($row[$sectionIndex] ?? '');
            $division = trim($row[$divisionIndex] ?? '');

            return !(
                $section === '' || $section === 'NULL' ||
                $division === '' || $division === 'NULL'
            );
        });

        $totalRows = $filteredRows->count();


        $chunkSize = 300;
        $totalChunks = ceil($totalRows / $chunkSize);

        Cache::put("upload_progress_{$this->jobId}", [
            'status' => 'processing',
            'processed' => 0,
            'inserted' => 0,
            'total' => 0,
            'total_chunks' => $totalChunks,
            'completed_chunks' => 0,
            'progress' => 0,
            'bim_count' => 0, // exclude header row
        ], 600);

        foreach ($dataRows->chunk($chunkSize) as $chunk) {
            ProcessExcelChunkJob::dispatch(
                $this->jobId,
                $chunk,
                $headerRow,
                $this->mappings,
                $this->importBatchNo,
                $this->dataId,
                $this->assetTableName,
                $this->bimResults,
                $this->createdBy,
                $this->createdByName,
                $this->projectData,
                $this->type, //default/ cobie
            );
        }

        Log::info("Parent job dispatched all chunk jobs: {$this->jobId}");
    }
}
