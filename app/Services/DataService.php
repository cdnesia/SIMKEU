<?php

namespace App\Services;

use App\Models\Bipot;
use App\Models\BipotPerAngkatan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DataService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    private function expandTerms(int $start, int $end): array
    {
        $y  = intdiv($start, 10);
        $s  = $start % 10;
        $ye = intdiv($end,   10);
        $se = $end   % 10;
        $out = [];
        while ($y < $ye || ($y === $ye && $s <= $se)) {
            $out[] = $y * 10 + $s;
            $s++;
            if ($s > 2) {
                $s = 1;
                $y++;
            }
        }
        return $out;
    }

    public function bipot()
    {
        $raw = BipotPerAngkatan::with([
            'programKuliah',
            'bipotSemester.bipot',
        ])->get();

        $result = $raw->groupBy('kode_prodi')->map(function ($prodiGroup) {
            return $prodiGroup
                ->groupBy('kode_tahun')
                ->sortKeys()
                ->map(function ($tahunGroup) {

                    return $tahunGroup->groupBy(function ($item) {
                        return $item->programKuliah->nama_program_perkuliahan ?? 'Tanpa Program';
                    })
                        ->sortKeys(SORT_NATURAL)
                        ->map(function ($programGroup) {

                            $semesterGrouped = [];

                            foreach ($programGroup as $angkatan) {
                                foreach ($angkatan->bipotSemester as $semester) {

                                    $semesterKey = $semester->semester;

                                    $semesterGrouped[$semesterKey][] = [
                                        'id'    => $semester->id,
                                        'nama_bipot' => $semester->bipot->nama_bipot ?? null,
                                        'nominal'    => $semester->nominal,
                                        'semester'   => $semester->semester,
                                        'status_mahasiswa' => $semester->status_mahasiswa_list,
                                        'jenis_masuk' => $semester->jenis_masuk_mahasiswa_list,
                                    ];
                                }
                            }

                            ksort($semesterGrouped);

                            return $semesterGrouped;
                        });
                });
        });

        return $result->toArray();
    }

    public function tahunAkademikAktif($kodeProdi = null)
    {
        $today = Carbon::today()->toDateString();
        $query = DB::connection('db_siade')
            ->table('master_tahun_akademik')
            ->where('status', 'A')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today);
        if ($kodeProdi) {
            $query->whereJsonContains('kode_program_studi', $kodeProdi);
        }
        return $query->value('kode_tahun_akademik');
    }

    public function prodi($kodeProdi = null)
    {
        $query = DB::connection('db_siade')
            ->table('master_program_studi as p')
            ->join('master_fakultas as f', 'p.fakultas_id', 'f.id')
            ->where('p.status', 'A')
            ->select('p.kode_program_studi', 'p.nama_program_studi_idn', 'f.nama_fakultas_idn');

        if ($kodeProdi) {
            $query->where('kode_program_studi', $kodeProdi);
        }

        return $query->get();
    }
    public function kelas($id = null)
    {
        $query = DB::connection('db_siade')
            ->table('master_kelas_perkuliahan');
        if ($id) {
            $query->where('id', $id);
        }

        return $query->get();
    }

    public function generateTagihan($npm = null, $tahunAkademik = null, $jenis_tagihan = null)
    {
        $mahasiswa = DB::connection('db_siade')->table('master_mahasiswa')->where('npm', $npm)->first();

        if (!$mahasiswa) {
            return null;
        }
        if (!$jenis_tagihan) {
            $jenis_tagihan = 'SPP';
        }
        if (!$tahunAkademik) {
            $tahunAkademik = $this->tahunAkademikAktif();
        }

        $npm = $mahasiswa->npm;
        $nama_mahasiswa = $mahasiswa->nama_mahasiswa;
        $va_code = substr($tahunAkademik, -3) . str_pad($mahasiswa->va_code, 6, '0', STR_PAD_LEFT);
        $id_program_kuliah = $mahasiswa->program_kuliah_id;
        $kode_program_studi = $mahasiswa->kode_program_studi;
        $tahun_angkatan = $mahasiswa->tahun_angkatan;
        $semester = collect($this->expandTerms($tahun_angkatan, $tahunAkademik))->count();

        $exists = DB::connection('db_payment')
            ->table('tagihan')
            ->where('npm', $npm)
            ->where('id_kelas_perkuliahan', $id_program_kuliah)
            ->where('tahun_akademik', $tahunAkademik)
            ->where('jenis_tagihan', $jenis_tagihan)
            ->exists();

        if ($exists) {
            return [
                'success' => false,
                'message' => 'Tagihan ' . $jenis_tagihan . ' sudah ada untuk mahasiswa ini.',
            ];
        }

        $tagihanRaw = DB::table('master_bipot_per_angkatan as bpa')
            ->leftJoin('master_bipot_per_semester as bps', 'bpa.id', 'bps.id_bipot_angkatan')
            ->leftJoin('master_bipot as b', 'b.id', 'bps.id_bipot')
            ->where('bpa.kode_tahun', $tahun_angkatan)
            ->where('bpa.kode_prodi', $kode_program_studi)
            ->where('bpa.id_program_kuliah', $id_program_kuliah)
            ->where('bps.semester', $semester)
            ->get();

        $rincian_tagihan = [];
        $total_tagihan = 0;
        foreach ($tagihanRaw as $key => $value) {
            $rincian_tagihan[] = [
                'id_bipot' => $value->id_bipot,
                'nama_bipot' => $value->nama_bipot,
                'nominal' => $value->nominal
            ];
            $total_tagihan += $value->nominal;
        }

        $insert = [
            'id_record_tagihan' => now()->format('YmdHisv') . rand(100, 999),
            'npm' => $npm,
            'nama_mahasiswa' => $nama_mahasiswa,
            'nomor_tagihan' => $va_code,
            'id_kelas_perkuliahan' => $id_program_kuliah,
            'nama_kelas_perkuliahan' => $this->kelas($id_program_kuliah)->value('nama_program_perkuliahan'),
            'nama_fakultas' => $this->prodi($kode_program_studi)->value('nama_fakultas_idn'),
            'kode_program_studi' => $kode_program_studi,
            'nama_program_studi' => $this->prodi($kode_program_studi)->value('nama_program_studi_idn'),
            'tahun_akademik' => $tahunAkademik,
            'total_tagihan' => $total_tagihan,
            'nominal_ditagih' => $total_tagihan,
            'detail_tagihan' => json_encode($rincian_tagihan),
            'waktu_berakhir' => Carbon::now()->addMonths(2)->endOfDay(),
            'jenis_tagihan' => $jenis_tagihan,
        ];

        DB::connection('db_payment')->table('tagihan')->insert($insert);
        return [
            'success' => true,
            'message' => 'Tagihan ' . $jenis_tagihan . ' berhasil dibuat.',
            'data' => $insert
        ];
    }
    public function generateVA($tahunAkademik)
    {
        $tahunPrefix = substr($tahunAkademik, -3);
        $prefix = $tahunPrefix . '99';
        $lastTagihan = DB::connection('db_payment')->table('tagihan')
            ->where('nomor_tagihan', 'like', $prefix . '%')
            ->orderBy('nomor_tagihan', 'desc')
            ->first();

        if ($lastTagihan) {
            $lastNumber = (int)substr($lastTagihan->nomor_tagihan, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $nomorUrut = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        return $prefix . $nomorUrut;
    }
    public function saveFlagging($idTagihan, $idBipot, $dibayar)
    {
        $masterBipot = Bipot::get()->keyBy('id');

        if (!isset($masterBipot[$idBipot])) {
            return [
                'success' => false,
                'message' => 'Bipot tidak ditemukan!'
            ];
        }

        DB::beginTransaction();
        try {
            $tagihanRaw = DB::connection('db_payment')->table('tagihan')
                ->where('id', $idTagihan)
                ->first();

            if (!$tagihanRaw) {
                return [
                    'success' => false,
                    'message' => 'Tagihan tidak ditemukan!'
                ];
            }

            $id_record_tagihan = $tagihanRaw->id_record_tagihan;
            $tahun_akademik = $tagihanRaw->tahun_akademik;
            $npm = $tagihanRaw->npm;
            $nominal = $dibayar;
            $id_bipot = $idBipot;
            $nama_bipot = $masterBipot[$idBipot]->nama_bipot ?? 'Unknown';
            $bank = 'CASH';
            $metode = 'CASH';

            $cekPembayaran = DB::table('tbl_pembayaran_mahasiswa')
                ->where('id_record_tagihan', $id_record_tagihan)
                ->where('id_bipot', $id_bipot)
                ->where('tahun_akademik', $tahun_akademik)->first();
            if ($cekPembayaran) {
                return [
                    'success' => false,
                    'message' => "Tagihan sudah ada didata pembayaran."
                ];
            }

            DB::table('tbl_pembayaran_mahasiswa')->insert([
                'id_record_tagihan' => $id_record_tagihan,
                'tahun_akademik' => $tahun_akademik,
                'npm' => $npm,
                'nominal' => $nominal,
                'id_bipot' => $id_bipot,
                'nama_bipot' => $nama_bipot,
                'bank' => $bank,
                'metode' => $metode,
                'waktu_transaksi' => Carbon::now()
            ]);

            $totalDibayar = DB::table('tbl_pembayaran_mahasiswa')
                ->where('id_record_tagihan', $id_record_tagihan)
                ->sum('nominal');

            DB::connection('db_payment')->table('tagihan')
                ->where('id', $idTagihan)
                ->update([
                    'nominal_terbayar' => $totalDibayar,
                    'nominal_ditagih' => $tagihanRaw->total_tagihan - $totalDibayar
                ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Flagging tagihan berhasil.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
}
