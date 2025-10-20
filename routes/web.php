<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadToolController;

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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : view('welcome');
});

// Protected dashboard route (only for logged-in users)
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Breeze provides built-in auth routes (login, register, etc.)
require __DIR__.'/auth.php';

// Custom logout route (optional, Breeze already includes /logout via POST)
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

// Your existing controller routes
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/uploadtool', [UploadToolController::class, 'index'])->middleware(['auth'])->name('uploadtool');
Route::post('/uploadtool', [UploadToolController::class, 'store'])->middleware(['auth'])->name('uploadtool.store');

Route::post('/uploadtool/execute-update', [UploadToolController::class, 'executeUpdate'])
    ->middleware(['auth'])
    ->name('uploadtool.execute');

Route::get('/uploadtool/progress', [UploadToolController::class, 'getProgress'])
    ->middleware(['auth'])
    ->name('uploadtool.progress');
