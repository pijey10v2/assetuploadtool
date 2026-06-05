<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use PDO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HierarchyController extends Controller
{
    public function index(Request $request)
    {
        $importBatch = $request->query('import_batch');

        // Use $importBatch as needed
        return view('hierarchy.setupHierarchy', compact('importBatch'));
    }

    public function getHierarchyData()
    {
        $response = Http::get(
            env('JOGET_API_URL'),
            [
                'mode' => 'get_asset_hierarchy',
                'type' => 'cobie'
            ]
        );

        return response()->json(
            $response->json()
        );
    }

    public function getHierarchyDataAll()
    {
        $response = Http::get(
            env('JOGET_API_URL'),
            [
                'mode' => 'get_asset_hierarchy_all',
                'type' => 'cobie'
            ]
        );

        return response()->json(
            $response->json()
        );
    }

    public function getLevel1()
    {
        $response = Http::get(
            env('JOGET_API_URL'),
            [
                'mode' => 'get_hierarchylevel_1',
                'type' => 'cobie'
            ]
        );

        return response()->json(
            $response->json()
        );
    }

    public function saveMapping(Request $request)
    {
        \Log::info([
            'mapping_count' => count($request->mappings ?? [])
        ]);

        $response = Http::timeout(300)
            ->asForm()
            ->post(
                env('JOGET_API_URL'),
                [
                    'mode' => 'update_hierarchy_mapping',
                    'mappings' => json_encode(
                        $request->mappings
                    )
                ]
            );

        return response()->json([
            'status_code' => $response->status(),
            'raw' => $response->body(),
            'json' => $response->json()
        ]);
    }

    public function recentImports()
    {
        $response = Http::get(
            env('JOGET_API_URL'),
            [
                'mode' => 'get_recent_importbatch_nos',
                'type' => 'cobie'
            ]
        );

        $data = $response->json();

        return view(
            'hierarchy.recentImports',
            [
                'imports' => $data['assets'] ?? []
            ]
        );
    }
}

?>