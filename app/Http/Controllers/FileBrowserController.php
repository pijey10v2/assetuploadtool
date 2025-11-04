<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FileBrowserController extends Controller
{
    public function getBimFiles(Request $request)
    {
        return $this->getFilesPaginated($request, 'bimfiles', ['bim']);
    }

    public function getExcelFiles(Request $request)
    {
        return $this->getFilesPaginated($request, 'uploads', ['xlsx', 'xls']);
    }

    private function getFilesPaginated(Request $request, $directory, array $extensions)
    {
        $page = $request->input('page', 1);
        $perPage = 20;

        $files = collect(Storage::files($directory))
            ->filter(fn($file) => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $extensions))
            ->sortByDesc(fn($file) => Storage::lastModified($file))
            ->values();

        $paginated = $files
            ->forPage($page, $perPage)
            ->map(fn($file) => [
                'name' => basename($file),
                'size' => round(Storage::size($file) / 1024, 2) . ' KB',
                'modified' => Carbon::createFromTimestamp(Storage::lastModified($file))->format('Y-m-d H:i:s'),
            ])
            ->values();

        return response()->json([
            'status' => 'success',
            'files' => $paginated,
            'total' => $files->count(),
            'page' => (int) $page,
            'per_page' => $perPage,
        ]);
    }
}
