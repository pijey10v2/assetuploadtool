<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadToolController;
use App\Http\Controllers\FileBrowserController;
use App\Http\Controllers\BimUploadController;

// Breeze provides built-in auth routes (login, register, etc.)
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider and contain
| the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Custom logout route (optional, Breeze already includes /logout via POST)
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Upload tool page
    Route::get('/uploadtool', [UploadToolController::class, 'index'])->name('uploadtool');
    // Upload tool form submission
    Route::post('/uploadtool', [UploadToolController::class, 'store'])->name('uploadtool.store');
    // Upload tool execute update
    Route::post('/uploadtool/execute-update', [UploadToolController::class, 'executeUpdate'])->name('uploadtool.execute');
    // Upload tool progress update
    Route::get('/uploadtool/progress', [UploadToolController::class, 'getProgress'])->name('uploadtool.progress');
    // Get i-BIM files list
    Route::get('/files/bim', [FileBrowserController::class, 'getBimFiles'])->name('files.bim');
    // Get Excel files list
    Route::get('/files/excel', [FileBrowserController::class, 'getExcelFiles'])->name('files.excel');
    // Dashboard page - Clear bim files
    Route::delete('/files/clear-bim', [FileBrowserController::class, 'clearBimFiles'])->name('files.clearBim');
    // Dashboard page - Clear Excel files
    Route::delete('/files/clear-excel', [FileBrowserController::class, 'clearExcelFiles'])->name('files.clearExcel');
    // Upload i-BIM page
    Route::get('/bimupload', [BimUploadController::class, 'index'])->name('bimupload.index');
    // Upload i-BIM form submission
    Route::post('/bimupload', [BimUploadController::class, 'store'])->name('bimupload.store');
    // Clear All i-BIM Files
    Route::delete('/bimupload/clear', [BimUploadController::class, 'clearBim'])->name('bimupload.clearBim');
    // Clear selected i-BIM file
    Route::post('/bimupload/clear-bim', [BimUploadController::class, 'clearSelectedBimFiles'])->name('bimupload.clearBimFiles');
    // Get i-BIM files list for BIM upload page
    Route::get('/bimupload/bim', [BimUploadController::class, 'listBimFiles'])->name('bimupload.listBim');
});