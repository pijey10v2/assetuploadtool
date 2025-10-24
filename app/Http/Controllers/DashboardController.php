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
            ->map(function ($file) {
                $ts = Storage::lastModified($file);
                $exact = Carbon::createFromTimestamp($ts)->format('Y-m-d H:i:s');
                $human = Carbon::createFromTimestamp($ts)->diffForHumans();

                return [
                    'name' => basename($file),
                    'size' => round(Storage::size($file) / 1024, 2), // KB
                    'modified_timestamp' => $ts,     // for sorting
                    'modified_exact'     => $exact,  // precise display
                    'modified_human'     => $human,  // human display
                    'modified'           => $human,  // alias to keep old Blade working
                ];
            })
            ->sortByDesc('modified_timestamp')
            //->take(20) // limit
            ->values();

        $bimCount = $bimFiles->count();
        $latestBim = $bimFiles->first();

        // UPLOADED EXCEL FILES (storage/app/uploads)
        $excelFiles = collect(Storage::files('uploads'))
        ->filter(fn($file) => preg_match('/\.(xlsx|xls|csv)$/i', $file))
        ->map(function ($file) {
            $ts = Storage::lastModified($file);
            $exact = Carbon::createFromTimestamp($ts)->format('Y-m-d H:i:s');
            $human = Carbon::createFromTimestamp($ts)->diffForHumans();

            return [
                'name' => basename($file),
                'size' => round(Storage::size($file) / 1024, 2), // KB
                'modified_timestamp' => $ts,     // for sorting
                'modified_exact'     => $exact,  // precise display
                'modified_human'     => $human,  // human display
                'modified'           => $human,  // alias to keep old Blade working
            ];
        })
        ->sortByDesc('modified_timestamp')
        //->take(20) // limit
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
