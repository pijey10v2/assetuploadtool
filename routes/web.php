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

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// Custom logout route (optional, Breeze already includes /logout via POST)
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    Route::prefix('uploadtool')->group(function () {
        // Upload tool index page
        Route::get('/', [UploadToolController::class, 'index'])->name('uploadtool');
        // Upload tool form submission
        Route::post('/', [UploadToolController::class, 'store'])->name('uploadtool.store');
        // Upload tool execute update
        Route::post('/execute-update', [UploadToolController::class, 'executeUpdate'])->name('uploadtool.execute');
        // Upload tool progress update
        Route::get('/progress', [UploadToolController::class, 'getProgress'])->name('uploadtool.progress');
    });

    Route::prefix('bimupload')->group(function () {
        // Upload i-BIM page
        Route::get('/', [BimUploadController::class, 'index'])->name('bimupload.index');
        // Upload i-BIM form submission
        Route::post('/', [BimUploadController::class, 'store'])->name('bimupload.store');
        // Clear All i-BIM Files
        Route::post('/clear', [BimUploadController::class, 'clearBim'])->name('bimupload.clearBim');
        // Clear selected i-BIM file
        Route::post('/clear-bim', [BimUploadController::class, 'clearSelectedBimFiles'])->name('bimupload.clearBimFiles');
        // Get i-BIM files list for BIM upload page
        Route::get('/bim', [BimUploadController::class, 'listBimFiles'])->name('bimupload.listBim');
    });

    Route::prefix('files')->group(function () {
        // Get i-BIM files list
        Route::get('/bim', [FileBrowserController::class, 'getBimFiles'])->name('files.bim');
        // Get Excel files list
        Route::get('/excel', [FileBrowserController::class, 'getExcelFiles'])->name('files.excel');
        // Dashboard page - Clear bim files
        Route::post('/clear-bim', [FileBrowserController::class, 'clearBimFiles'])->name('files.clearBim');
        // Dashboard page - Clear Excel files
        Route::post('/clear-excel', [FileBrowserController::class, 'clearExcelFiles'])->name('files.clearExcel');
        // Dashboard page - Clear selected bim files
        Route::post('/clear-selected-bim', [FileBrowserController::class, 'clearSelectedBimFiles'])->name('files.clearSelectedBim');
        // Dashboard page - Clear selected excel files
        Route::post('/clear-selected-excel', [FileBrowserController::class, 'clearSelectedExcelFiles'])->name('files.clearSelectedExcel');
    });

    Route::prefix('dropdown')->group(function () {
        // Dropdown list for project names
        Route::get('/projects', [UploadToolController::class, 'getProjects'])->name('api.projects');
        // Dropdown list for layers (data id) based on selected projects.project id
        Route::get('/layers/{projectId}', [UploadToolController::class, 'getLayersByProject'])->name('api.layers.byProject');
    });

});