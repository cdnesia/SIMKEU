<?php

namespace App\Http\Controllers;

use App\Models\Bipot;
use App\Models\BipotPerAngkatan;
use App\Models\BipotPerSemester;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    private $modul = 'master.sync';
    public function __construct()
    {

        view()->share('modul', $this->modul);
    }
    public function index()
    {
        $d['bipot'] = Bipot::get()->count();
        $d['bipot_per_angkatan'] = BipotPerAngkatan::get()->count();
        $d['bipot_per_semester'] = BipotPerSemester::get()->count();
        $d['tagihan'] = DB::connection('db_payment')->table('tagihan')->get()->count();
        $d['pembayaran'] = DB::table('tbl_pembayaran_mahasiswa')->get()->count();
        return view('master.view', $d);
    }
    public function bipot()
    {
        $now = now();
        $totalNew = 0;
        $existingCodes = Bipot::pluck('id')->toArray();

        try {
            DB::beginTransaction();
            DB::connection('simkeu_old1')->table('keu_bipotnama')
                ->orderBy('id')
                ->where('NA', 'N')
                ->chunk(500, function ($rows) use (&$totalNew, $now, $existingCodes) {
                    $insertData = [];
                    foreach ($rows as $m) {
                        if (in_array($m->id, $existingCodes)) {
                            continue;
                        }

                        $insertData[] = [
                            'id' => $m->id,
                            'nama_bipot' => $m->nama,
                            'trxid' => $m->trxid,
                            'urutan' => $m->urutan,
                        ];

                        $totalNew++;
                    }

                    if (!empty($insertData)) {
                        Bipot::insert($insertData);
                    }
                });

            DB::commit();

            return redirect()->back()->with('success', "Sinkronisasi berhasil. Data baru: $totalNew");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Sinkronisasi gagal: ' . $e->getMessage());
        }
    }
    public function bipotPerAngkatan()
    {
        $now = now();
        $totalNew = 0;
        $existingCodes = BipotPerAngkatan::pluck('id')->toArray();
        $prodis = DB::connection('db_siade')->table('master_program_studi')->get()->pluck('kode_program_studi');

        try {
            DB::beginTransaction();
            DB::connection('simkeu_old1')->table('keu_bipot')
                ->whereIn('prodi', $prodis)
                ->where('NA', 'N')
                ->orderBy('id')
                ->chunk(500, function ($rows) use (&$totalNew, $now, $existingCodes) {
                    $insertData = [];
                    foreach ($rows as $m) {
                        if (in_array($m->id, $existingCodes)) {
                            continue;
                        }

                        $insertData[] = [
                            'id' => $m->id,
                            'kode_tahun' => $m->kode . '1',
                            'nama_tahun' => $m->tahun,
                            'id_program_kuliah' => match ((int)$m->kelas) {
                                1, 2 => 1,
                                3 => 2,
                                4 => 3,
                                default => null
                            },
                            'kode_prodi' => $m->prodi,
                        ];

                        $totalNew++;
                    }

                    if (!empty($insertData)) {
                        BipotPerAngkatan::insert($insertData);
                    }
                });

            DB::commit();

            return redirect()->back()->with('success', "Sinkronisasi berhasil. Data baru: $totalNew");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Sinkronisasi gagal: ' . $e->getMessage());
        }
    }
    public function bipotPerSemester()
    {
        $now = now();
        $totalNew = 0;
        $existingCodes = BipotPerSemester::pluck('id')->toArray();
        $bipot = Bipot::pluck('id')->toArray();

        $bipotAngkatan = BipotPerAngkatan::pluck('id')->toArray();

        try {
            DB::beginTransaction();
            DB::connection('simkeu_old1')->table('keu_bipot2')
                ->whereIn('bipotnama', $bipot)
                ->whereIn('bipot', $bipotAngkatan)
                ->where('NA', 'N')
                ->orderBy('id')
                ->chunk(500, function ($rows) use (&$totalNew, $now, $existingCodes) {
                    $insertData = [];
                    foreach ($rows as $m) {
                        if (in_array($m->id, $existingCodes)) {
                            continue;
                        }

                        $insertData[] = [
                            'id' => $m->id,
                            'id_bipot_angkatan' => $m->bipot,
                            'id_bipot' => $m->bipotnama,
                            'nominal' => $m->nominal,
                            'semester' => $m->semester,
                            'status_awal' => json_encode($m->status_awal
                                ? array_map('intval', explode('.', trim($m->status_awal, '.')))
                                : []),
                            'status_mahasiswa' => json_encode($m->status_mhsw
                                ? array_map('intval', explode('.', trim($m->status_mhsw, '.')))
                                : []),
                        ];

                        $totalNew++;
                    }

                    if (!empty($insertData)) {
                        BipotPerSemester::insert($insertData);
                    }
                });

            DB::commit();

            return redirect()->back()->with('success', "Sinkronisasi berhasil. Data baru: $totalNew");
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Sinkronisasi gagal: ' . $e->getMessage());
        }
    }
    public function tagihan()
    {
        $prodi = DB::connection('db_siade')
            ->table('master_program_studi as p')
            ->join('master_fakultas as f', 'p.fakultas_id', 'f.id')
            ->where('p.status', 'A')
            ->select(
                'p.kode_program_studi',
                'p.nama_program_studi_idn',
                'f.nama_fakultas_idn'
            )
            ->get()
            ->keyBy(function ($item) {
                return strtoupper(
                    trim(
                        str_replace('-', ' ', $item->nama_program_studi_idn)
                    )
                );
            });

        $totalNew = 0;
        try {
            DB::connection('db_payment')->statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::connection('db_payment')->table('tagihan')->truncate();
            DB::connection('db_payment')->statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::beginTransaction();
            DB::connection('simkeu_old')->table('bjambi_biller')
                ->whereIn('tahun_akademik', ['20251', '20252', '20241', '20242', '20231', '20232'])
                ->orderBy('id')
                ->chunk(500, function ($rows) use (&$totalNew, $prodi) {
                    $insertData = [];
                    foreach ($rows as $m) {

                        $detailTagihan = json_decode($m->detail, true);
                        $idBipot = explode('-', $m->bipot2_id);

                        $hasil = [];

                        $namaKeys = array_keys($detailTagihan);
                        $nominalValues = array_values($detailTagihan);

                        foreach ($namaKeys as $index => $nama) {

                            $hasil[] = [
                                'nominal'    => $nominalValues[$index] ?? 0,
                                'id_bipot'   => isset($idBipot[$index]) ? (int)$idBipot[$index] : null,
                                'nama_bipot' => $nama,
                            ];
                        }

                        $insertData[] = [
                            'id' => $m->id,
                            'id_record_tagihan' => $m->id_record_tagihan,
                            'npm' => $m->nomor_induk,
                            'nama_mahasiswa' => $m->nama,
                            'nomor_tagihan' => $m->nomor_pembayaran,
                            'id_kelas_perkuliahan' => $m->nama_program === 'REGULER A' ? '1' : '2',
                            'nama_kelas_perkuliahan' => $m->nama_program === 'REGULER A' ? 'REGULER A' : 'REGULER B',
                            'nama_fakultas' => $prodi[Str::upper($m->nama_prodi)]->nama_fakultas_idn,
                            'kode_program_studi' => $prodi[Str::upper($m->nama_prodi)]->kode_program_studi,
                            'nama_program_studi' => $prodi[Str::upper($m->nama_prodi)]->nama_program_studi_idn,
                            'tahun_akademik' => $m->tahun_akademik,
                            'total_tagihan' => $m->bipot2_jumlah_nominal,
                            'nominal_ditagih' => $m->bipot2_jumlah_nominal,
                            'nominal_terbayar' => $m->terbayar_nominal,
                            'detail_tagihan' => json_encode($hasil),
                            'status_aktif' => $m->is_tagihan_aktif == 1 ? 'Y' : 'T',
                            'waktu_berakhir' => Carbon::now()->addMonths(2)->endOfDay(),
                        ];

                        $totalNew++;
                    }
                    DB::connection('db_payment')->table('tagihan')->insert($insertData);
                });
            DB::commit();

            return redirect()->back()->with('success', "Sinkronisasi berhasil. Data baru: $totalNew");
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Sinkronisasi gagal: ' . $e->getMessage());
        }
    }
    public function pembayaran()
    {
        $bipot = Bipot::pluck('nama_bipot', 'id')->toArray();

        $tagihan = DB::connection('db_payment')
            ->table('tagihan')
            ->get()
            ->groupBy('npm')
            ->map(function ($items) {
                return $items->keyBy('tahun_akademik');
            })->toArray();

        $totalNew = 0;
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('tbl_pembayaran_mahasiswa')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::beginTransaction();
            DB::connection('simkeu_old1')->table('keu_bayarmhsw as b')
                ->select('b.id', 'b.tahun', 'b.bukti_setoran', 'b.bipotmhsw', 'b.nim', 'b.pmb', 'b.tanggal', 'b.jam', 'b.bank', 'k.bipotnama', 'k.dibayar', 'b.keterangan')
                ->join('keu_bipotmhsw as k', 'b.bipotmhsw', 'k.id')
                ->where('b.NA', 'N')
                ->where('k.NA', 'N')
                ->whereIn('b.tahun', ['20251', '20252', '20241', '20242', '20231', '20232'])
                ->orderBy('b.id')
                ->chunk(500, function ($rows) use (&$totalNew, $bipot, $tagihan) {
                    $insertData = [];
                    foreach ($rows as $m) {
                        $datetime = Carbon::parse($m->tanggal . ' ' . $m->jam)
                            ->format('Y-m-d H:i:s');

                        $id_record_tagihan = $tagihan[$m->nim][$m->tahun]->id_record_tagihan ?? null;

                        if (!$id_record_tagihan) {
                            continue;
                        }


                        $insertData[] = [
                            'id' => $m->id,
                            'id_record_tagihan' => $id_record_tagihan,
                            'npm' => $m->nim,
                            'pmb' => $m->pmb,
                            'tahun_akademik' => $m->tahun,
                            'id_bipot' => $m->bipotnama,
                            'nama_bipot' => $bipot[$m->bipotnama],
                            'nominal' => $m->dibayar,
                            'waktu_transaksi' => $datetime,
                            'bank' => $m->bank == 4 ? '2' : '1',
                            'metode' => $m->bukti_setoran == 'CASH' ? 'CASH' : 'H2H',
                        ];

                        $totalNew++;
                    }
                    DB::table('tbl_pembayaran_mahasiswa')->insert($insertData);
                });
            DB::commit();

            return redirect()->back()->with('success', "Sinkronisasi berhasil. Data baru: $totalNew");
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Sinkronisasi gagal: ' . $e->getMessage());
        }
    }
}
