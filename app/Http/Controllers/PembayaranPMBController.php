<?php

namespace App\Http\Controllers;

use App\Models\Bipot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranPMBController extends Controller
{
    private $modul = 'pembayaran';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }
    public function index()
    {
        $dataPembayaranRaw = DB::table('tbl_pembayaran_mahasiswa')
            ->select(
                'tahun_akademik',
                'npm',
                'id_bipot',
                'nama_bipot',
                'waktu_transaksi',
                'bank',
                DB::raw('SUM(nominal) as nominal')
            )
            ->where('tahun_akademik', 'like', 'UMJA%')
            ->groupBy('tahun_akademik', 'npm', 'id_bipot', 'nama_bipot')
            ->get();

        $masterBipot = Bipot::select('id as id_bipot', 'nama_bipot')
            ->get()
            ->keyBy('id_bipot');

        $data = $dataPembayaranRaw
            ->groupBy('tahun_akademik')
            ->sortKeysDesc()
            ->map(function ($tahunGroup) use ($masterBipot) {
                return $tahunGroup
                    ->groupBy('npm')
                    ->sortKeysDesc()
                    ->map(function ($npmGroup) use ($masterBipot) {
                        $pembayaranPerBipot = $npmGroup->keyBy('id_bipot');
                        $detail = $masterBipot->map(function ($bipot) use ($pembayaranPerBipot) {
                            $nominal = isset($pembayaranPerBipot[$bipot->id_bipot])
                                ? $pembayaranPerBipot[$bipot->id_bipot]->nominal
                                : 0;

                            return [
                                'id_bipot'   => $bipot->id_bipot,
                                'nama_bipot' => $bipot->nama_bipot,
                                'nominal'    => $nominal,
                            ];
                        })->values();

                        return [
                            'total_terbayar' => $detail->sum('nominal'),
                            'detail' => $detail
                        ];
                    });
            });

        $d['pembayaran'] = $data;
        return view('pembayaran.view', $d);
    }
}
