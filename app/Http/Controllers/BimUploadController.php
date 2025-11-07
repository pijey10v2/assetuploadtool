<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class BimUploadController extends Controller
{
    public function index()
    {
        return view('bimupload.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bimfiles.*' => [
                'required',
                'file',
                'max:51200', // 50 MB limit
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if ($ext !== 'bim') {
                        $fail("Only .bim files are allowed.");
                    }
                },
            ],
        ]);

        $uploadedFiles = [];

        foreach ($request->file('bimfiles') as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $timestamp = now()->format('Y-m-d_H-i-s');
            $fileName = $originalName . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('bimfiles', $fileName);
            $uploadedFiles[] = [
                'name' => $fileName,
                'path' => $path,
                'size' => round(Storage::size($path) / 1024, 2) . ' KB',
            ];
        }

        return response()->json([
            'message' => 'Files uploaded successfully.',
            'uploaded' => $uploadedFiles
        ]);
    }

    public function clearBim()
    {
        $files = Storage::files('bimfiles');
        Storage::delete($files);

        // Check if directory is now empty
        $remainingFiles = Storage::files('bimfiles');
        $isEmpty = empty($remainingFiles);

         return response()->json([
            'message' => "All i.BIM files cleared successfully.",
            'remaining' => count($remainingFiles),
            'isEmpty' => $isEmpty
        ]);
    }

    public function clearSelectedBimFiles(Request $request)
    {
        $files = $request->input('files', []);
        $deletedCount = 0;

        foreach ($files as $file) {
            $path = 'bimfiles/' . basename($file);
            if (Storage::exists($path)) {
                Storage::delete($path);
                $deletedCount++;
            }
        }

        return response()->json([
            'message' => "Deleted {$deletedCount} file(s) successfully."
        ]);
    }


    public function listBimFiles(Request $request)
    {
        $search = $request->query('search', '');
        $page = $request->query('page', 1);
        $perPage = 10;

        $files = collect(Storage::files('bimfiles'))
            ->map(fn($file) => [
                'name' => basename($file),
                'size' => round(Storage::size($file) / 1024, 2) . ' KB',
                'uploaded' => \Carbon\Carbon::createFromTimestamp(Storage::lastModified($file))->diffForHumans(),
            ])
            ->sortByDesc('uploaded');

        // Apply search filter
        if (!empty($search)) {
            $files = $files->filter(fn($file) => str_contains(strtolower($file['name']), strtolower($search)));
        }

        // Paginate results manually
        $total = $files->count();
        $items = $files->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => route('bimupload.listBim'),
            'query' => ['search' => $search]
        ]);

        $loaded = min(($paginator->currentPage() - 1) * $paginator->perPage() + $paginator->count(), $paginator->total());

        return response()->json([
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'loaded' => $loaded,
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $perPage,
            ]
        ]);
    }

}
