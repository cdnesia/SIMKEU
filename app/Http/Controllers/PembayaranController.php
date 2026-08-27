<?php

namespace App\Http\Controllers;

use App\Exports\PembayaranExport;
use App\Models\Bipot;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PembayaranController extends Controller
{
    private $modul = 'pembayaran';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }

    /**
     * Build the pivoted (tahun -> npm -> per-bipot nominal) payment dataset,
     * optionally filtered by tahun akademik and/or a single BIPOT type.
     * Shared by index() (screen) and export() (Excel) so both stay in sync.
     */
    private function filteredPembayaran(Request $request)
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

        return [$pembayaran, $firstDetail];
    }

    public function index(Request $request)
    {
        [$pembayaran, $firstDetail] = $this->filteredPembayaran($request);

        $d['pembayaran'] = $pembayaran;
        $d['firstDetail'] = $firstDetail;
        $d['bipot_list'] = Bipot::orderBy('urutan')->get();
        $d['tahun_akademik_list'] = DB::table('tbl_pembayaran_mahasiswa')
            ->select('tahun_akademik')
            ->distinct()
            ->orderByDesc('tahun_akademik')
            ->pluck('tahun_akademik');
        $d['tahun_akademik_terpilih'] = $request->query('tahun_akademik');
        $d['bipot_terpilih'] = $request->query('bipot');

        return view('pembayaran.view', $d);
    }

    public function export(Request $request)
    {
        [$pembayaran, $firstDetail] = $this->filteredPembayaran($request);

        return Excel::download(new PembayaranExport($pembayaran, $firstDetail), 'data-pembayaran.xlsx');
    }
    public function create() {}
    public function edit($id, Request $request, DataService $service)
    {
        $id = Crypt::decrypt($id);
        abort_unless($request->ajax(), 404);
        $tagihanRaw = DB::connection('db_payment')->table('tagihan')
            ->where('id', $id)
            ->first();

        if (!$tagihanRaw) {
            return [];
        }
        $data = DB::table('tbl_pembayaran_mahasiswa')
            ->where('id_record_tagihan', $tagihanRaw->id_record_tagihan)->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('nominal', function ($item) {
                return 'Rp ' . number_format($item->nominal, 0, ',', '.');
            })
            ->addColumn('aksi', function ($item) {
                $id = Crypt::encrypt($item->id);
                return '
                    <button data-id="' . $id . '" class="btn btn-danger btn-sm btn-delete pb-2"><i class="bx bx-message-square-x me-0"></i></button>
                    ';
            })

            ->rawColumns(['aksi'])
            ->make(true);
    }
    public function store(Request $request, DataService $service)
    {
        abort_unless($request->ajax(), 404);
        $res = $service->saveFlagging($request->tagihan_id, $request->bipot_id, $request->dibayar);

        return response()->json($res);
    }
    public function update() {}
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $cek = DB::table('tbl_pembayaran_mahasiswa')
            ->where('id', $id)
            ->delete();

        if (!$cek) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dihapus'
        ]);
    }
}
