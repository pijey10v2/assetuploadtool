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
        $projectData
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
    }

    public function handle()
    {
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

        $totalRows = $dataRows->count();
        $chunkSize = 300;
        $totalChunks = ceil($totalRows / $chunkSize);

        Cache::put("upload_progress_{$this->jobId}", [
            'status' => 'processing',
            'processed' => 0,
            'inserted' => 0,
            'total' => $totalRows,
            'total_chunks' => $totalChunks,
            'completed_chunks' => 0,
            'progress' => 0
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
                $this->projectData
            );
        }

        Log::info("Parent job dispatched all chunk jobs: {$this->jobId}");
    }
}
