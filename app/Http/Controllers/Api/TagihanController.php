<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bipot;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\select;

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
    public function cekPembayaranPMB(Request $request)
    {
        $request->validate([
            'npm' => 'required|string',
        ]);

        $cek_pembayaran = DB::table('tbl_pembayaran_mahasiswa')
            ->where('npm', $request->npm)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cek_pembayaran,
        ]);
    }
    public function generateTagihanKKN(Request $request, DataService $dataService)
    {
        $validator = Validator::make($request->all(), [
            'npm' => 'required|string',
            'tahun_akademik' => 'required|string',
            'kegiatan_mahasiswa_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $npm = $request->npm;
        $tahunAkademik = $request->tahun_akademik;
        $kegiatan_mahasiswa_id = $request->kegiatan_mahasiswa_id;

        $mahasiswa = DB::connection('db_siade')->table('master_mahasiswa')->where('npm', $npm)->first();
        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan',
            ], 409);
        }

        $nama_mahasiswa = $mahasiswa->nama_mahasiswa;
        $va_code = '9' . substr($tahunAkademik, -2) . str_pad($mahasiswa->va_code, 6, '0', STR_PAD_LEFT);
        $id_program_kuliah = $mahasiswa->program_kuliah_id;
        $kode_program_studi = $mahasiswa->kode_program_studi;

        $exists = DB::connection('db_payment')
            ->table('tagihan')
            ->where('npm', $npm)
            ->where('id_kelas_perkuliahan', $id_program_kuliah)
            ->where('tahun_akademik', $tahunAkademik)
            ->where('jenis_tagihan', 'KKN')
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan KKN sudah ada untuk mahasiswa ini.',
            ], 409);
        }

        $tagihanRaw = DB::connection('db_siade')->table('tbl_kegiatan_mahasiswa as tkm')
            ->join('dev_simkeu_new.master_bipot as b', 'b.id', 'tkm.id_bipot')
            ->select('b.id', 'b.nama_bipot', 'tkm.biaya_pendaftaran')
            ->where('tkm.id', $kegiatan_mahasiswa_id)
            ->get();

        $rincian_tagihan = [];
        $total_tagihan = 0;
        foreach ($tagihanRaw as $key => $value) {
            $rincian_tagihan[] = [
                'id_bipot' => $value->id,
                'nama_bipot' => $value->nama_bipot,
                'nominal' => $value->biaya_pendaftaran,
            ];
            $total_tagihan += $value->biaya_pendaftaran;
        }

        $insert = [
            'id_record_tagihan' => now()->format('YmdHisv') . rand(100, 999),
            'npm' => $npm,
            'nama_mahasiswa' => $nama_mahasiswa,
            'nomor_tagihan' => $va_code,
            'id_kelas_perkuliahan' => $id_program_kuliah,
            'nama_kelas_perkuliahan' => $dataService->kelas($id_program_kuliah)->value('nama_program_perkuliahan'),
            'nama_fakultas' => $dataService->prodi($kode_program_studi)->value('nama_fakultas_idn'),
            'kode_program_studi' => $kode_program_studi,
            'nama_program_studi' => $dataService->prodi($kode_program_studi)->value('nama_program_studi_idn'),
            'tahun_akademik' => $tahunAkademik,
            'detail_tagihan' => json_encode($rincian_tagihan),
            'total_tagihan' => $total_tagihan,
            'nominal_ditagih' => $total_tagihan,
            'waktu_berakhir' => Carbon::now()->addMonths(6)->endOfDay(),
            'jenis_tagihan' => 'KKN',
        ];

        DB::connection('db_payment')->table('tagihan')->insert($insert);
        $result = [
            'success' => true,
            'message' => 'Tagihan KKN berhasil dibuat.',
        ];

        return response()->json($result);
    }
    public function cekTagihanKKN(Request $request)
    {
        $request->validate([
            'npm' => 'required|string',
            'tahun_akademik' => 'nullable|string',
        ]);

        $result = DB::connection('db_payment')
            ->table('tagihan')
            ->where('npm', $request->npm)
            ->where('jenis_tagihan', 'KKN')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Tagihan KKN sudah ada untuk mahasiswa ini.',
            'data' => $result
        ]);
    }
}
