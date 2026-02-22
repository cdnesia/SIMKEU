<?php

namespace App\Http\Controllers;

use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PembayaranController extends Controller
{
    private $modul = 'pembayaran';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }
    public function index() {}
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
