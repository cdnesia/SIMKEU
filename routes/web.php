<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BipotController;
use App\Http\Controllers\BipotPerAngkatanController;
use App\Http\Controllers\BipotPerSemesterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JadwalPembayaranController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PerpanjanganPerMahasiswaController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



Route::middleware(['auth','checkPermission'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::resource('users', UsersController::class)->except('show');
    Route::resource('roles', RolesController::class)->except('show');
    Route::resource('permissions', PermissionsController::class)->only('index', 'create', 'store', 'destroy');

    Route::resource('bipot', BipotController::class)->except('show');
    Route::resource('bipot-per-angkatan', BipotPerAngkatanController::class);

    Route::resource('jadwal-pembayaran', JadwalPembayaranController::class)->except('show');
    Route::resource('perpanjangan-per-mahasiswa', PerpanjanganPerMahasiswaController::class)->except('show');
    Route::resource('tagihan', TagihanController::class);
    Route::resource('pembayaran', PembayaranController::class);


    Route::get('/master/sync/index', [SyncController::class, 'index'])->name('master.sync.index');
    Route::get('/master/sync/bipot', [SyncController::class, 'bipot'])->name('master.sync.bipot');
    Route::get('/master/sync/tagihan', [SyncController::class, 'tagihan'])->name('master.sync.tagihan');
    Route::get('/master/sync/pembayaran', [SyncController::class, 'pembayaran'])->name('master.sync.pembayaran');
    Route::get('/master/sync/bipot-per-angkatan', [SyncController::class, 'bipotPerAngkatan'])->name('master.sync.bipotperangkatan');
    Route::get('/master/sync/bipot-per-semester', [SyncController::class, 'bipotPerSemester'])->name('master.sync.bipotpersemester');
});
