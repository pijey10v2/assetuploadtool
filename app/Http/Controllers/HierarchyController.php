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
}

?>