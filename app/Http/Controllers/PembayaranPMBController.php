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
    public function index(Request $request)
    {
        $tahunAkademik = $request->query('tahun_akademik');
        $bipotId = $request->query('bipot');

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
            ->where('tahun_akademik', 'like', '%UMJA%')
            ->when($tahunAkademik, fn($q, $v) => $q->where('tahun_akademik', $v))
            ->when($bipotId, fn($q, $v) => $q->where('id_bipot', $v))
            ->groupBy('tahun_akademik', 'npm', 'id_bipot', 'nama_bipot')
            ->get();

        $masterBipot = Bipot::select('id as id_bipot', 'nama_bipot')
            ->when($bipotId, fn($q, $v) => $q->where('id', $v))
            ->orderBy('urutan')
            ->get()
            ->keyBy('id_bipot');

        $pembayaran = $dataPembayaranRaw
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

        $firstTahun = $pembayaran->first();
        $firstNpm = $firstTahun ? collect($firstTahun)->first() : null;
        $firstDetail = $firstNpm['detail'] ?? $masterBipot->map(fn($b) => ['nama_bipot' => $b->nama_bipot])->values();

        $d['pembayaran'] = $pembayaran;
        $d['firstDetail'] = $firstDetail;
        $d['bipot_list'] = Bipot::orderBy('urutan')->get();
        $d['tahun_akademik_list'] = DB::table('tbl_pembayaran_mahasiswa')
            ->select('tahun_akademik')
            ->where('tahun_akademik', 'like', '%UMJA%')
            ->distinct()
            ->orderByDesc('tahun_akademik')
            ->pluck('tahun_akademik');
        $d['tahun_akademik_terpilih'] = $tahunAkademik;
        $d['bipot_terpilih'] = $bipotId;

        return view('pembayaran.view', $d);
    }
}
