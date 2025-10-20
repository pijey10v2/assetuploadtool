<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // BIM FILES (storage/app/bimfiles)
        $bimFiles = collect(Storage::files('bimfiles'))
            ->filter(fn($file) => str_ends_with(strtolower($file), '.bim'))
            ->map(fn($file) => [
                'name' => basename($file),
                'size' => round(Storage::size($file) / 1024, 2), // KB
                'modified' => Carbon::createFromTimestamp(Storage::lastModified($file))->diffForHumans(),
            ])
            ->sortByDesc('modified')
            ->values();

        $bimCount = $bimFiles->count();
        $latestBim = $bimFiles->first();

        // UPLOADED EXCEL FILES (storage/app/uploads)
        $excelFiles = collect(Storage::files('uploads'))
            ->filter(fn($file) => preg_match('/\.(xlsx|xls|csv)$/i', $file))
            ->map(fn($file) => [
                'name' => basename($file),
                'size' => round(Storage::size($file) / 1024, 2), // KB
                'modified' => Carbon::createFromTimestamp(Storage::lastModified($file))->diffForHumans(),
            ])
            ->sortByDesc('modified')
            ->values();

        $excelCount = $excelFiles->count();
        $latestExcel = $excelFiles->first();

        // Summary cards
        $summary = [
            'bimCount' => $bimCount,
            'excelCount' => $excelCount,
            'lastUpload' => $latestExcel['modified'] ?? 'N/A',
            // 'pendingTasks' => rand(1, 5), // placeholder for now
            'pendingTasks' => 0, // placeholder for now
        ];

        // Pass everything to the dashboard view
        return view('dashboard', [
            'bimFiles' => $bimFiles,
            'excelFiles' => $excelFiles,
            'summary' => $summary,
        ]);
    }
}
