<?php

namespace App\Http\Controllers;

use App\Exports\PembayaranExport;
use App\Models\Bipot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PembayaranPMBController extends Controller
{
    private $modul = 'pembayaran';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }

    private function filteredPembayaran(Request $request)
    {
        $tahunAkademikList = DB::table('tbl_pembayaran_mahasiswa')
            ->select('tahun_akademik')
            ->where('tahun_akademik', 'like', '%UMJA%')
            ->distinct()
            ->orderByDesc('tahun_akademik')
            ->pluck('tahun_akademik');

        // Tanpa filter tahun akademik eksplisit, default ke tahun terakhir agar query tidak berat.
        $tahunAkademik = $request->has('tahun_akademik')
            ? $request->query('tahun_akademik')
            : $tahunAkademikList->first();
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

        $namaMahasiswa = DB::connection('db_payment')->table('tagihan')
            ->select('npm', 'nama_mahasiswa')
            ->whereIn('npm', $dataPembayaranRaw->pluck('npm')->unique())
            ->get()
            ->unique('npm')
            ->keyBy('npm');

        $pembayaran = $dataPembayaranRaw
            ->groupBy('tahun_akademik')
            ->sortKeysDesc()
            ->map(function ($tahunGroup) use ($masterBipot, $namaMahasiswa) {
                return $tahunGroup
                    ->groupBy('npm')
                    ->sortKeysDesc()
                    ->map(function ($npmGroup, $npm) use ($masterBipot, $namaMahasiswa) {
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
                            'nama_mahasiswa' => $namaMahasiswa[$npm]->nama_mahasiswa ?? '-',
                            'total_terbayar' => $detail->sum('nominal'),
                            'detail' => $detail
                        ];
                    });
            });

        $firstTahun = $pembayaran->first();
        $firstNpm = $firstTahun ? collect($firstTahun)->first() : null;
        $firstDetail = $firstNpm['detail'] ?? $masterBipot->map(fn($b) => ['nama_bipot' => $b->nama_bipot])->values();

        return [$pembayaran, $firstDetail, $tahunAkademikList, $tahunAkademik];
    }

    public function index(Request $request)
    {
        [$pembayaran, $firstDetail, $tahunAkademikList, $tahunAkademikTerpilih] = $this->filteredPembayaran($request);

        $d['pembayaran'] = $pembayaran;
        $d['firstDetail'] = $firstDetail;
        $d['bipot_list'] = Bipot::orderBy('urutan')->get();
        $d['tahun_akademik_list'] = $tahunAkademikList;
        $d['tahun_akademik_terpilih'] = $tahunAkademikTerpilih;
        $d['bipot_terpilih'] = $request->query('bipot');

        return view('pembayaran.view', $d);
    }

    public function export(Request $request)
    {
        [$pembayaran, $firstDetail] = $this->filteredPembayaran($request);

        return Excel::download(new PembayaranExport($pembayaran, $firstDetail), 'data-pembayaran-pmb.xlsx');
    }
}
