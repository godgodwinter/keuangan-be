<?php

use App\Http\Controllers\admin\adminAdministratorController;
use App\Http\Controllers\admin\adminKategoriController;
use App\Http\Controllers\admin\adminProsesController;
use App\Http\Controllers\admin\adminTransaksiController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// php get .env

// $prefix = getenv('API_VERSION') ? getenv('API_VERSION') : 'v1';
Route::post("admin/auth/login", [AuthController::class, 'login']);
// Route::post('/admin/auth/register', [AuthController::class, 'register'])->name('admin.auth.register');
// Route::middleware('api')->group(function () {
Route::middleware('auth:api')->group(
    function () {
        Route::get("admin/auth/me", [AuthController::class, 'me']);
        // get My Data and New Token
        Route::post("admin/auth/profile", [AuthController::class, 'refresh']);
        // update
        Route::put("admin/auth/profile", [AuthController::class, 'update']);

        Route::get('/admin/kategori', [adminKategoriController::class, 'index']);
        Route::post('/admin/kategori', [adminKategoriController::class, 'store']);
        Route::get('/admin/kategori/{item}', [adminKategoriController::class, 'edit']);
        Route::put('/admin/kategori/{item}', [adminKategoriController::class, 'update']);
        Route::delete('/admin/kategori/{item}', [adminKategoriController::class, 'destroy']);

        Route::get('/admin/users', [adminAdministratorController::class, 'index']);
        Route::post('/admin/users', [adminAdministratorController::class, 'store']);
        Route::get('/admin/users/{item}', [adminAdministratorController::class, 'edit']);
        Route::put('/admin/users/{item}', [adminAdministratorController::class, 'update']);
        Route::delete('/admin/users/{item}', [adminAdministratorController::class, 'destroy']);
        Route::delete('/admin/users/{item}/force', [adminAdministratorController::class, 'destroyForce']);


        Route::get('/admin/transaksi', [adminTransaksiController::class, 'index']);
        Route::post('/admin/transaksi', [adminTransaksiController::class, 'store']);
        Route::get('/admin/transaksi/{item}', [adminTransaksiController::class, 'edit']);
        Route::put('/admin/transaksi/{item}', [adminTransaksiController::class, 'update']);
        Route::delete('/admin/transaksi/{item}', [adminTransaksiController::class, 'destroy']);
        Route::delete('/admin/transaksi/{item}/force', [adminTransaksiController::class, 'destroyForce']);


        Route::get('/admin/rekap', [adminTransaksiController::class, 'rekap']); //inputan:month + year


        Route::post('/admin/proses/cleartemp ', [adminProsesController::class, 'clearTemp']);
    }
);
