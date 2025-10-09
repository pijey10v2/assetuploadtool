<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadToolController extends Controller
{
    public function index()
    {
        return view('uploadtool');
    }

    public function store(Request $request)
    {
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('uploads');
            return back()->with('success', "File uploaded to: $path");
        }
        return back()->with('error', 'No file selected');
    }
}
