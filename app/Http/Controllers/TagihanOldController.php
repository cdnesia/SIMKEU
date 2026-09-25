<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TagihanOldController extends Controller
{
    private $modul = 'tagihan-old';

    // Koneksi & tabel tagihan sistem lama
    private $koneksi = 'simaku_old';
    private $tabel = 'tabel_tagihan';

    // Nilai status_pembayaran saat tagihan ditandai lunas secara manual
    private $statusLunas = 'Terbayar';

    public function __construct()
    {
        view()->share('modul', $this->modul);
    }

    private function query()
    {
        return DB::connection($this->koneksi)->table($this->tabel);
    }

    private function formatUang($nominal)
    {
        return 'Rp ' . number_format((float) $nominal, 0, ',', '.');
    }

    private function formatTanggal($tanggal)
    {
        return $tanggal ? Carbon::parse($tanggal)->translatedFormat('d F Y H:i') : '';
    }

    private function findOrFail($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $e) {
            abort(404);
        }

        $tagihan = $this->query()->where('id', $id)->first();
        abort_unless($tagihan, 404);

        return $tagihan;
    }

    private function sudahDibayar($tagihan)
    {
        return !empty($tagihan->status_pembayaran);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    $id = Crypt::encrypt($row->id);
                    $btn = '<div class="btn-group">';
                    if (!$this->sudahDibayar($row)) {
                        $btn .= '<button type="button" title="Flagging" data-id="' . $id . '" class="btn btn-success btn-sm btn-flagging"><i class="bx bx-flag me-0"></i></button>';
                        $btn .= '<button type="button" title="Perpanjang" data-id="' . $id . '" class="btn btn-primary btn-sm btn-perpanjang"><i class="bx bx-refresh me-0"></i></button>';
                        $btn .= '<button type="button" title="Ubah" data-id="' . $id . '" class="btn btn-warning btn-sm btn-edit"><i class="bx bx-message-square-edit me-0"></i></button>';
                    }
                    $btn .= '<button type="button" title="Hapus" data-id="' . $id . '" class="btn btn-danger btn-sm btn-delete"><i class="bx bx-message-square-x me-0"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->editColumn('status_pembayaran', function ($row) {
                    if ($this->sudahDibayar($row)) {
                        return '<span class="badge bg-success w-100">Pembayaran Berhasil [' . e($row->bank) . ']</span>';
                    }
                    if (Carbon::parse($row->waktu_kadaluarsa)->gte(Carbon::now())) {
                        return '<span class="badge bg-primary w-100">Menunggu Pembayaran</span>';
                    }
                    return '<span class="badge bg-danger w-100">Pembayaran Kadaluarsa</span>';
                })
                ->editColumn('rincian', function ($row) {
                    $rincian = '';
                    foreach (json_decode($row->rincian) ?? [] as $value) {
                        $rincian .= '<span class="d-block">' .
                            e(Str::upper(str_replace('_', ' ', $value->kode_rincian ?? '-'))) . ' : ' .
                            $this->formatUang($value->nominal ?? 0) .
                            '</span>';
                    }
                    return $rincian;
                })
                ->editColumn('info', function ($row) {
                    $info = '';
                    foreach (json_decode($row->info) ?? [] as $value) {
                        $info .= '<span class="d-block">' .
                            e($value->label_key ?? '-') . ' : ' . e($value->label_value ?? '-') .
                            '</span>';
                    }
                    return $info;
                })
                ->editColumn('total_nominal', fn($row) => $this->formatUang($row->total_nominal))
                ->editColumn('waktu_berlaku', fn($row) => $this->formatTanggal($row->waktu_berlaku))
                ->editColumn('waktu_kadaluarsa', fn($row) => $this->formatTanggal($row->waktu_kadaluarsa))
                ->editColumn('waktu_pembayaran', fn($row) => $this->formatTanggal($row->waktu_pembayaran))
                ->rawColumns(['aksi', 'status_pembayaran', 'rincian', 'info'])
                ->make(true);
        }

        return view('tagihan-old.view');
    }

    // Data satu tagihan (JSON) untuk mengisi modal ubah/perpanjang/flagging
    public function show($id)
    {
        $tagihan = $this->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id'               => $id,
                'total_nominal'    => (int) $tagihan->total_nominal,
                'rincian'          => json_decode($tagihan->rincian, true) ?? [],
                'waktu_berlaku'    => $tagihan->waktu_berlaku ? Carbon::parse($tagihan->waktu_berlaku)->format('Y-m-d\TH:i') : null,
                'waktu_kadaluarsa' => $tagihan->waktu_kadaluarsa ? Carbon::parse($tagihan->waktu_kadaluarsa)->format('Y-m-d\TH:i') : null,
            ],
        ]);
    }

    // Ubah rincian nominal & waktu berlaku tagihan
    public function update(Request $request, $id)
    {
        $tagihan = $this->findOrFail($id);

        if ($this->sudahDibayar($tagihan)) {
            return response()->json(['success' => false, 'message' => 'Tagihan yang sudah dibayar tidak dapat diubah'], 422);
        }

        $request->validate([
            'waktu_berlaku'       => 'required|date',
            'waktu_kadaluarsa'    => 'required|date|after:waktu_berlaku',
            'rincian'             => 'required|array|min:1',
            'rincian.*.nominal'   => 'required|numeric|min:0',
        ]);

        // Pertahankan struktur rincian lama, hanya nominal yang diganti
        $rincianLama = json_decode($tagihan->rincian, true) ?? [];
        $rincianBaru = [];
        foreach ($rincianLama as $i => $item) {
            if (isset($request->rincian[$i]['nominal'])) {
                $item['nominal'] = (int) $request->rincian[$i]['nominal'];
            }
            $rincianBaru[] = $item;
        }

        $this->query()->where('id', $tagihan->id)->update([
            'rincian'          => json_encode($rincianBaru),
            'total_nominal'    => collect($rincianBaru)->sum('nominal'),
            'waktu_berlaku'    => Carbon::parse($request->waktu_berlaku),
            'waktu_kadaluarsa' => Carbon::parse($request->waktu_kadaluarsa),
        ]);

        return response()->json(['success' => true, 'message' => '✓ Data berhasil diperbarui']);
    }

    // Perpanjang waktu kadaluarsa tagihan
    public function perpanjang(Request $request, $id)
    {
        $tagihan = $this->findOrFail($id);

        if ($this->sudahDibayar($tagihan)) {
            return response()->json(['success' => false, 'message' => 'Tagihan yang sudah dibayar tidak dapat diperpanjang'], 422);
        }

        $request->validate([
            'waktu_kadaluarsa' => 'required|date|after:now',
        ]);

        $this->query()->where('id', $tagihan->id)->update([
            'waktu_kadaluarsa' => Carbon::parse($request->waktu_kadaluarsa),
        ]);

        return response()->json(['success' => true, 'message' => '✓ Masa berlaku tagihan berhasil diperpanjang']);
    }

    // Tandai tagihan lunas secara manual
    public function flagging(Request $request, $id)
    {
        $tagihan = $this->findOrFail($id);

        if ($this->sudahDibayar($tagihan)) {
            return response()->json(['success' => false, 'message' => 'Tagihan sudah berstatus lunas'], 422);
        }

        $request->validate([
            'bank'             => 'required|string|max:15',
            'waktu_pembayaran' => 'required|date|before_or_equal:now',
        ]);

        $this->query()->where('id', $tagihan->id)->update([
            'status_pembayaran' => $this->statusLunas,
            'bank'              => $request->bank,
            'waktu_pembayaran'  => Carbon::parse($request->waktu_pembayaran),
        ]);

        return response()->json(['success' => true, 'message' => '✓ Tagihan berhasil ditandai lunas']);
    }

    public function destroy($id)
    {
        $tagihan = $this->findOrFail($id);

        $this->query()->where('id', $tagihan->id)->delete();

        return response()->json(['success' => true, 'message' => '✓ Data berhasil dihapus']);
    }
}
