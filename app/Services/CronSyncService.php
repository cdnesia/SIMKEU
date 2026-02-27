<?php

namespace App\Services;

use App\Models\Bipot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CronSyncService
{
    public function tagihan(): int
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
                    trim(str_replace('-', ' ', $item->nama_program_studi_idn))
                );
            });

        $totalNew = 0;

        try {
            DB::connection('db_payment')->beginTransaction();

            DB::connection('simkeu_old')
                ->table('bjambi_biller')
                ->where('nomor_induk', 'like', 'UMJA2026%')
                ->orderBy('id')
                ->chunk(500, function ($rows) use (&$totalNew, $prodi) {

                    $insertData = [];
                    $recordIds = collect($rows)->pluck('id_record_tagihan')->toArray();

                    $existingIds = DB::connection('db_payment')
                        ->table('tagihan')
                        ->whereIn('id_record_tagihan', $recordIds)
                        ->pluck('id_record_tagihan')
                        ->toArray();

                    foreach ($rows as $m) {
                        if (in_array($m->id_record_tagihan, $existingIds)) {
                            continue;
                        }

                        $detailTagihan = json_decode($m->detail, true) ?? [];
                        $idBipot = explode('-', $m->bipot2_id ?? '');

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

                        $namaProdiKey = Str::upper($m->nama_prodi);

                        // ✅ Hindari error jika prodi tidak ditemukan
                        if (!isset($prodi[$namaProdiKey])) {
                            continue;
                        }

                        $insertData[] = [
                            'id_record_tagihan' => $m->id_record_tagihan,
                            'npm' => $m->nomor_induk,
                            'nama_mahasiswa' => $m->nama,
                            'nomor_tagihan' => $m->nomor_pembayaran,
                            'id_kelas_perkuliahan' => $m->nama_program === 'REGULER A' ? '1' : '2',
                            'nama_kelas_perkuliahan' => $m->nama_program === 'REGULER A' ? 'REGULER A' : 'REGULER B',
                            'nama_fakultas' => $prodi[$namaProdiKey]->nama_fakultas_idn,
                            'kode_program_studi' => $prodi[$namaProdiKey]->kode_program_studi,
                            'nama_program_studi' => $prodi[$namaProdiKey]->nama_program_studi_idn,
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

                    // ✅ Insert hanya jika ada data
                    if (!empty($insertData)) {
                        DB::connection('db_payment')
                            ->table('tagihan')
                            ->insert($insertData);
                    }
                });

            // ✅ COMMIT DI CONNECTION YANG BENAR
            DB::connection('db_payment')->commit();

            return $totalNew;
        } catch (\Throwable $e) {

            // ✅ ROLLBACK DI CONNECTION YANG BENAR
            DB::connection('db_payment')->rollBack();

            throw $e;
        }
    }
    public function SyncPembayaranNew()
    {
        $pembayaran = DB::connection('db_payment')
            ->table('pembayaran')
            ->select(
                'pembayaran.id as pembayaran_id',
                'pembayaran.jumlah_pembayaran',
                'pembayaran.id_record_tagihan',
                'pembayaran.id_record_pembayaran',
                'pembayaran.waktu_transaksi_bank',
                'pembayaran.nomor_tagihan',
                'pembayaran.from_bank',
                'pembayaran.proses',
                'tagihan.*'
            )
            ->join('tagihan', 'pembayaran.id_record_tagihan', '=', 'tagihan.id_record_tagihan')
            ->where('proses', '0')
            ->orderBy('pembayaran.id')
            ->get();

        $totalProcessed = 0;
        foreach ($pembayaran as $row) {
            DB::transaction(function () use ($row) {

                $jumlahBayar = (int) $row->jumlah_pembayaran;
                $npm = $row->npm;
                $tahunAkademik = $row->tahun_akademik;

                if ($jumlahBayar <= 0) {
                    return;
                }

                $details = json_decode($row->detail_tagihan, true);
                if (!is_array($details)) {
                    return;
                }

                foreach ($details as $detail) {

                    if ($jumlahBayar <= 0) {
                        break;
                    }

                    $idBipot = $detail['id_bipot'];
                    $nominalDetail = (int) $detail['nominal'];

                    $sudahDibayar = DB::table('tbl_pembayaran_mahasiswa')
                        ->where('id_record_tagihan', $row->id_record_tagihan)
                        ->where('npm', $npm)
                        ->where('tahun_akademik', $tahunAkademik)
                        ->where('id_bipot', $idBipot)
                        ->sum('nominal');

                    $sisaTagihan = $nominalDetail - $sudahDibayar;

                    if ($sisaTagihan <= 0) {
                        continue;
                    }

                    $bayarSekarang = min($jumlahBayar, $sisaTagihan);

                    $existing = DB::table('tbl_pembayaran_mahasiswa')
                        ->where('id_record_tagihan', $row->id_record_tagihan)
                        ->where('npm', $npm)
                        ->where('tahun_akademik', $tahunAkademik)
                        ->where('id_bipot', $idBipot)
                        ->first();

                    if ($existing) {

                        DB::table('tbl_pembayaran_mahasiswa')
                            ->where('id', $existing->id)
                            ->update([
                                'nominal' => $existing->nominal + $bayarSekarang,
                            ]);
                    } else {

                        DB::table('tbl_pembayaran_mahasiswa')->insert([
                            'id_record_tagihan'   => $row->id_record_tagihan,
                            'id_record_pembayaran' => $row->id_record_pembayaran,
                            'tahun_akademik'      => $tahunAkademik,
                            'npm'                 => $npm,
                            'id_bipot'            => $idBipot,
                            'nama_bipot'          => $detail['nama_bipot'],
                            'nominal'             => $bayarSekarang,
                            'waktu_transaksi'     => $row->waktu_transaksi_bank,
                            'bank'                => $row->from_bank == 'BSI' ? '1' : '2',
                            'metode'              => 'H2H',
                        ]);
                    }

                    $jumlahBayar -= $bayarSekarang;
                }

                DB::connection('db_payment')
                    ->table('pembayaran')
                    ->where('id', $row->pembayaran_id)
                    ->update(['proses' => '1']);
            });
            $totalProcessed++;
        }
        return $totalProcessed;
    }
}
