<?php

use App\Http\Controllers\Api\TagihanController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API Laravel 12 aktif'
    ]);
});

Route::post('/cek-tagihan', [TagihanController::class, 'cekTagihanFromSimawa'])->middleware('verifyHmac');
Route::post('/cek-tagihan-pmb', [TagihanController::class, 'cekPembayaranPMB'])->middleware('verifyHmac');
Route::post('/generate-tagihan', [TagihanController::class, 'generateTagihanFromSimawa'])->middleware('verifyHmac');
Route::post('/riwayat-pembayaran', [TagihanController::class, 'riwayatPembayaran'])->middleware('verifyHmac');
Route::post('/cek-kontrak-matakuliah', [TagihanController::class, 'cekKontrakMk'])->middleware('verifyHmac');
Route::post('/data-bipot', [TagihanController::class, 'dataBipot'])->middleware('verifyHmac');
