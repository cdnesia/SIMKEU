<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bipot;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{
    protected $tagihanService;

    public function __construct(DataService $dataService)
    {
        $this->tagihanService = $dataService;
    }
    public function cekTagihanFromSimawa(Request $request)
    {
        $request->validate([
            'npm' => 'required|string',
            'tahun_akademik' => 'nullable|string',
            'jenis_tagihan' => 'nullable|string'
        ]);

        $result = $this->tagihanService->cekTagihan($request->npm);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan'
            ], 404);
        }

        return response()->json($result);
    }
    public function generateTagihanFromSimawa(Request $request)
    {
        $request->validate([
            'npm' => 'required|string',
            'tahun_akademik' => 'nullable|string',
            'jenis_tagihan' => 'nullable|string'
        ]);

        $result = $this->tagihanService->generateTagihan($request->npm);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan'
            ], 404);
        }

        return response()->json($result);
    }
    public function riwayatPembayaran(Request $request)
    {
        $request->validate([
            'npm' => 'required|string',
        ]);

        $tagihan = DB::connection('db_payment')
            ->table('tagihan')
            ->where('npm', $request->npm)
            ->get();

        if ($tagihan->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $hasil = $tagihan->map(function ($row) use ($request) {
            $detail_pembayaran = DB::table('tbl_pembayaran_mahasiswa')
                ->where('npm', $request->npm)
                ->where('tahun_akademik', $row->tahun_akademik)
                ->get()
                ->keyBy('id_bipot');

            $detail_tagihan = json_decode($row->detail_tagihan, true) ?? [];

            $detail_tagihan = array_map(function ($item) use ($detail_pembayaran) {

                $item['dibayar'] = 0;

                if (isset($detail_pembayaran[$item['id_bipot']])) {
                    $item['dibayar'] = rtrim(
                        rtrim($detail_pembayaran[$item['id_bipot']]->nominal, '0'),
                        '.'
                    );
                }

                return $item;
            }, $detail_tagihan);

            return [
                'tahun_akademik' => $row->tahun_akademik,
                'id_tagihan'     => $row->id,
                'detail'         => $detail_tagihan
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $hasil
        ]);
    }
    public function cekKontrakMk(Request $request)
    {
        $request->validate([
            'npm' => 'required|string',
            'tahun_akademik' => 'required|string',
        ]);

        $cek_pembayaran = DB::table('tbl_pembayaran_mahasiswa')
            ->where('npm', $request->npm)
            ->where('tahun_akademik', $request->tahun_akademik)
            ->get();

        $status = false;
        if ($cek_pembayaran->isNotEmpty()) {
            $status = true;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'boleh_kontrak' => $status
            ],
        ]);
    }
    public function dataBipot()
    {
        $data = Bipot::all()->toArray();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
