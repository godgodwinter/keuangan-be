<?php

use App\Http\Controllers\admin\adminProsesController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;



Route::post('/admin/auth/login', [AuthController::class, 'login'])->name('admin.auth.login');
// Route::post('/admin/auth/register', [AuthController::class, 'register'])->name('admin.auth.register');
// Route::middleware('api')->group(function () {
Route::middleware('auth:api')->group(
    function () {



        // Route::get('/admin/klasifikasi', [adminKlasifikasiAkademisController::class, 'index']);
        // Route::post('/admin/klasifikasi', [adminKlasifikasiAkademisController::class, 'store']);
        // Route::get('/admin/klasifikasi/{item}', [adminKlasifikasiAkademisController::class, 'edit']);
        // Route::put('/admin/klasifikasi/{item}', [adminKlasifikasiAkademisController::class, 'update']);
        // Route::delete('/admin/klasifikasi/{item}', [adminKlasifikasiAkademisController::class, 'destroy']);


        Route::post('/admin/proses/cleartemp ', [adminProsesController::class, 'clearTemp']);
    }
);
