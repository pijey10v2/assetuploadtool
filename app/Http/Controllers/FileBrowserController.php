<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FileBrowserController extends Controller
{
    public function getBimFiles(Request $request)
    {
        return $this->getFilesPaginated($request, 'bimfiles', ['bim']);
    }

    public function getExcelFiles(Request $request)
    {
        return $this->getFilesPaginated($request, 'uploads', ['xlsx', 'xls', 'csv']);
    }

    private function getFilesPaginated(Request $request, string $directory, array $extensions)
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = strtolower($request->input('search', ''));

        // Get all files and filter by allowed extensions
        $files = collect(Storage::files($directory))
            ->filter(function ($file) use ($extensions, $search) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $matchesExtension = in_array($extension, $extensions);

                // Optional search filtering by filename
                $matchesSearch = empty($search) || Str::contains(strtolower(basename($file)), $search);

                return $matchesExtension && $matchesSearch;
            })
            ->sortByDesc(fn($file) => Storage::lastModified($file))
            ->values();

        // Paginate manually (collection-based)
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
            'page' => $page,
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

    public function clearSelectedBimFiles(Request $request)
    {
        $files = $request->input('files', []);

        if (empty($files)) {
            return response()->json(['message' => 'No files selected for deletion.'], 400);
        }

        $deleted = 0;

        foreach ($files as $file) {
            // Ensure only files inside 'bimfiles' directory are deleted (safety check)
            $path = 'bimfiles/' . basename($file);

            if (Storage::exists($path)) {
                Storage::delete($path);
                $deleted++;
            }
        }

        return response()->json([
            'message' => "{$deleted} BIM file(s) deleted successfully.",
        ]);
    }


    public function clearSelectedExcelFiles(Request $request)
    {
        $files = $request->input('files', []);

        if (empty($files)) {
            return response()->json(['message' => 'No files selected for deletion.'], 400);
        }

        $deleted = 0;

        foreach ($files as $file) {
            $path = 'uploads/' . basename($file);

            if (Storage::exists($path)) {
                Storage::delete($path);
                $deleted++;
            }
        }

        return response()->json([
            'message' => "{$deleted} Excel file(s) deleted successfully.",
        ]);
    }


}
