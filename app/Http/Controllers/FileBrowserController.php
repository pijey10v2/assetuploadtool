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

    public function clearBimFiles()
    {
        return $this->clearFilesInDirectory('bimfiles');
    }

    public function clearExcelFiles()
    {
        return $this->clearFilesInDirectory('uploads');
    }

    private function clearFilesInDirectory(string $directory)
    {
        if (!Storage::exists($directory)) {
            return response()->json(['status' => 'error', 'message' => "Directory not found: {$directory}"], 404);
        }

        $files = Storage::files($directory);
        if (empty($files)) {
            return response()->json(['status' => 'info', 'message' => "No files found in {$directory}."]);
        }

        // Delete all files
        Storage::delete($files);

        return response()->json(['status' => 'success', 'message' => "All files in {$directory} have been cleared."]);
    }
}
