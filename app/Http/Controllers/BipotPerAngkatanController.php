<?php

namespace App\Http\Controllers;

use App\Models\Bipot;
use App\Models\BipotPerAngkatan;
use App\Models\BipotPerSemester;
use App\Models\KelasKuliah;
use App\Models\StatusMahasiswa;
use App\Models\StatusMasukMahasiswa;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class BipotPerAngkatanController extends Controller
{
    private $modul = 'bipot-per-angkatan';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(DataService $service)
    {
        $d['prodi'] = DB::connection('db_siade')->table('master_program_studi')->get();
        return view('bipot-perangkatan.view', $d);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->s === 'simpan') {
            $programStudi = $request->program_studi;
            $tahunAkademik = $request->tahun_akademik;

            $kelas = $request->kelas;

            $semester = [1, 2, 3, 4, 5, 6, 7, 8];
            foreach ($kelas as $key => $value) {
                $cek_bipot_pertahun = BipotPerAngkatan::where('kode_tahun', $tahunAkademik)
                    ->where('kode_prodi', $programStudi)
                    ->where('id_program_kuliah', $value)
                    ->first();

                if ($cek_bipot_pertahun) {
                    $id_bipot_angkatan = $cek_bipot_pertahun->id;
                    foreach ($semester as $keys => $values) {
                        $cek_bipot_persemester = BipotPerSemester::where('id_bipot_angkatan', $id_bipot_angkatan)
                            ->where('semester', $values)
                            ->first();
                        if (!$cek_bipot_persemester) {
                            BipotPerSemester::insert([
                                'id_bipot_angkatan' => $id_bipot_angkatan,
                                'semester' => $values
                            ]);
                        }
                    }
                } else {
                    $kodeTahun = $tahunAkademik;

                    $tahunAwal = substr($kodeTahun, 0, 4);
                    $tahunAkhir = $tahunAwal + 1;
                    $namaTahun = $tahunAwal . '/' . $tahunAkhir;

                    $cek_bipot_pertahun = BipotPerAngkatan::insertGetId([
                        'kode_tahun' => $tahunAkademik,
                        'nama_tahun' => $namaTahun,
                        'id_program_kuliah' => $value,
                        'kode_prodi' => $programStudi
                    ]);
                    $id_bipot_angkatan = $cek_bipot_pertahun;
                    foreach ($semester as $keys => $values) {
                        $cek_bipot_persemester = BipotPerSemester::where('id_bipot_angkatan', $id_bipot_angkatan)
                            ->where('semester', $values)
                            ->first();
                        if (!$cek_bipot_persemester) {
                            BipotPerSemester::insert([
                                'id_bipot_angkatan' => $id_bipot_angkatan,
                                'semester' => $values
                            ]);
                        }
                    }
                }
            }
            return redirect()->route('bipot-per-angkatan.index');
        }

        $d['prodi'] = DB::connection('db_siade')->table('master_program_studi')->get();
        $d['tahun_akademik'] = DB::connection('db_siade')->table('master_tahun_akademik')->get();
        $d['kelas'] = DB::connection('db_siade')->table('master_kelas_perkuliahan')->get();

        return view('bipot-perangkatan.form', $d);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $kode_prodi = Crypt::decrypt($request->kode_prodi);
        $cekIdAngkatan = BipotPerAngkatan::where('kode_tahun', $request->kode_tahun)->where('kode_prodi', $kode_prodi)->where('id_program_kuliah', $request->kelas_id)->first();

        if (!$cekIdAngkatan) {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal disimpan.',
            ]);
        }

        DB::table('master_bipot_per_semester')->insert([
            'id_bipot_angkatan' => $cekIdAngkatan->id,
            'id_bipot' => $request->id_bipot,
            'semester' => $request->semester,
            'nominal' => $request->nominal,
            'status_awal' => json_encode(array_map('intval', $request->status_awal ?? [])),
            'status_mahasiswa' => json_encode(array_map('intval', $request->status_mahasiswa ?? [])),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, DataService $service, $id)
    {
        $id = Crypt::decrypt($id);
        $tahunAkademik = $request->query('tahun_akademik');
        $kelasId = $request->query('kelas');
        $semester = $request->query('semester');

        $d['prodi_info'] = $service->prodi($id)->first();
        $d['bipot'] = $service->bipot($id, $tahunAkademik, $kelasId, $semester);
        $d['tahun_akademik_list'] = BipotPerAngkatan::where('kode_prodi', $id)
            ->select('kode_tahun', 'nama_tahun')
            ->distinct()
            ->orderBy('kode_tahun')
            ->get();

        $kelasIds = BipotPerAngkatan::where('kode_prodi', $id)
            ->distinct()
            ->pluck('id_program_kuliah')
            ->filter();
        $d['kelas_list'] = KelasKuliah::whereIn('id', $kelasIds)
            ->orderBy('nama_program_perkuliahan')
            ->get();

        $d['semester_list'] = range(1, 8);

        $d['tahun_akademik_terpilih'] = $tahunAkademik;
        $d['kelas_terpilih'] = $kelasId;
        $d['semester_terpilih'] = $semester;

        // Master data for the "copy to another prodi/kelas/tahun" target selects.
        $d['kode_prodi_saat_ini'] = $id;
        $d['prodi_master'] = DB::connection('db_siade')->table('master_program_studi')->orderBy('nama_program_studi_idn')->get();
        // Angkatan is only ever the Ganjil (odd) intake term — master_tahun_akademik has a
        // separate row per term (Ganjil ends in "1", Genap in "2"), so only Ganjil rows are
        // real angkatan years; shown as "YYYY/YYYY+1" to match the filter dropdown above.
        $d['tahun_akademik_master'] = DB::connection('db_siade')->table('master_tahun_akademik')
            ->where('kode_tahun_akademik', 'like', '%1')
            ->orderBy('kode_tahun_akademik', 'desc')
            ->get()
            ->map(function ($ta) {
                $tahunAwal = substr($ta->kode_tahun_akademik, 0, 4);
                $ta->nama_tahun = $tahunAwal . '/' . ($tahunAwal + 1);
                return $ta;
            });
        $d['kelas_master'] = DB::connection('db_siade')->table('master_kelas_perkuliahan')->orderBy('nama_program_perkuliahan')->get();

        return view('bipot-perangkatan.show', $d);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = DB::table('master_bipot_per_semester')->where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ]);
        }

        return response()->json([
            'id_bipot' => $data->id_bipot,
            'nominal' => $data->nominal,
            'status_mahasiswa' => array_map('intval', json_decode($data->status_mahasiswa ?? '[]')),
            'status_awal' => array_map('intval', json_decode($data->status_awal ?? '[]')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::table('master_bipot_per_semester')->where('id', $request->id)->update([
                'id_bipot' => $request->id_bipot,
                'nominal' => $request->nominal,
                'status_mahasiswa' => json_encode(array_map('intval', $request->status_mahasiswa ?? [])),
                'status_awal' => json_encode(array_map('intval', $request->status_awal ?? [])),
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Data BIPOT berhasil diedit'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengedit data'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::table('master_bipot_per_semester')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data BIPOT berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data'
            ]);
        }
    }

    /**
     * Copy all BIPOT items from one (prodi, kelas, tahun akademik, semester)
     * source to one or more target semesters — possibly in a different prodi,
     * kelas, and/or tahun akademik — so the user doesn't have to re-enter the
     * same items by hand. If the target angkatan doesn't exist yet, it (and
     * its 8 empty semester slots) is created first, mirroring create().
     */
    public function copySemester(Request $request, DataService $service)
    {
        $request->validate([
            'kode_tahun' => 'required|string',
            'kelas_id' => 'required',
            'source_semester' => 'required|integer|min:1|max:8',
            'target_kode_prodi' => 'required|string',
            'target_kode_tahun' => 'required|string',
            'target_kelas_id' => 'required',
            'target_semester' => 'required|array|min:1',
            'target_semester.*' => 'integer|min:1|max:8',
        ]);

        $kode_prodi = Crypt::decrypt($request->kode_prodi);
        $overwrite = $request->boolean('overwrite');

        $sourceAngkatan = BipotPerAngkatan::where('kode_tahun', $request->kode_tahun)
            ->where('kode_prodi', $kode_prodi)
            ->where('id_program_kuliah', $request->kelas_id)
            ->first();

        if (!$sourceAngkatan) {
            return response()->json([
                'success' => false,
                'message' => 'Data angkatan sumber tidak ditemukan.',
            ]);
        }

        $sourceItems = BipotPerSemester::where('id_bipot_angkatan', $sourceAngkatan->id)
            ->where('semester', $request->source_semester)
            ->whereNotNull('id_bipot')
            ->get();

        if ($sourceItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Semester sumber tidak memiliki data BIPOT untuk disalin.',
            ]);
        }

        $targetKodeProdi = $request->target_kode_prodi;
        $targetKodeTahun = $request->target_kode_tahun;
        $targetKelasId = $request->target_kelas_id;

        $targetSemesters = array_values(array_unique(array_map('intval', $request->target_semester)));

        // If target is the exact same angkatan as the source, copying the
        // source semester onto itself is a meaningless no-op — drop it.
        $isSameAngkatan = (string) $targetKodeProdi === (string) $kode_prodi
            && (string) $targetKodeTahun === (string) $request->kode_tahun
            && (string) $targetKelasId === (string) $request->kelas_id;

        if ($isSameAngkatan) {
            $targetSemesters = array_values(array_filter(
                $targetSemesters,
                fn($s) => $s != $request->source_semester
            ));
        }

        if (empty($targetSemesters)) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal satu semester tujuan yang valid.',
            ]);
        }

        $copied = [];
        $skipped = [];

        DB::transaction(function () use (
            $targetKodeProdi,
            $targetKodeTahun,
            $targetKelasId,
            $targetSemesters,
            $sourceItems,
            $overwrite,
            &$copied,
            &$skipped
        ) {
            $targetAngkatan = BipotPerAngkatan::where('kode_tahun', $targetKodeTahun)
                ->where('kode_prodi', $targetKodeProdi)
                ->where('id_program_kuliah', $targetKelasId)
                ->first();

            if ($targetAngkatan) {
                $targetAngkatanId = $targetAngkatan->id;
            } else {
                $tahunAwal = substr($targetKodeTahun, 0, 4);
                $tahunAkhir = $tahunAwal + 1;
                $namaTahun = $tahunAwal . '/' . $tahunAkhir;

                $targetAngkatanId = BipotPerAngkatan::insertGetId([
                    'kode_tahun' => $targetKodeTahun,
                    'nama_tahun' => $namaTahun,
                    'id_program_kuliah' => $targetKelasId,
                    'kode_prodi' => $targetKodeProdi,
                ]);

                foreach (range(1, 8) as $sm) {
                    BipotPerSemester::insert([
                        'id_bipot_angkatan' => $targetAngkatanId,
                        'semester' => $sm,
                    ]);
                }
            }

            foreach ($targetSemesters as $targetSemester) {
                // Drop the empty placeholder row (id_bipot NULL) created by
                // "Generate Tahun Akademik dan Semester" / above, if any — it
                // holds no real data and would otherwise show as a blank row.
                BipotPerSemester::where('id_bipot_angkatan', $targetAngkatanId)
                    ->where('semester', $targetSemester)
                    ->whereNull('id_bipot')
                    ->delete();

                $hasExisting = BipotPerSemester::where('id_bipot_angkatan', $targetAngkatanId)
                    ->where('semester', $targetSemester)
                    ->whereNotNull('id_bipot')
                    ->exists();

                if ($hasExisting && !$overwrite) {
                    $skipped[] = $targetSemester;
                    continue;
                }

                if ($hasExisting) {
                    BipotPerSemester::where('id_bipot_angkatan', $targetAngkatanId)
                        ->where('semester', $targetSemester)
                        ->delete();
                }

                foreach ($sourceItems as $item) {
                    DB::table('master_bipot_per_semester')->insert([
                        'id_bipot_angkatan' => $targetAngkatanId,
                        'id_bipot' => $item->id_bipot,
                        'semester' => $targetSemester,
                        'nominal' => $item->nominal,
                        'status_awal' => json_encode($item->status_awal ?? []),
                        'status_mahasiswa' => json_encode($item->status_mahasiswa ?? []),
                    ]);
                }

                $copied[] = $targetSemester;
            }
        });

        if (empty($copied)) {
            return response()->json([
                'success' => false,
                'message' => 'Semester tujuan sudah memiliki data. Centang "Timpa data" untuk menimpa.',
            ]);
        }

        $targetProdiNama = $service->prodi($targetKodeProdi)->value('nama_program_studi_idn') ?? $targetKodeProdi;
        $targetKelasNama = $service->kelas($targetKelasId)->value('nama_program_perkuliahan') ?? $targetKelasId;
        $targetTahunNama = DB::connection('db_siade')->table('master_tahun_akademik')
            ->where('kode_tahun_akademik', $targetKodeTahun)
            ->value('nama_tahun_akademik') ?? $targetKodeTahun;

        $message = "Data BIPOT berhasil disalin ke {$targetProdiNama} - {$targetKelasNama} - {$targetTahunNama}, semester "
            . implode(', ', $copied) . '.';
        if (!empty($skipped)) {
            $message .= ' Semester ' . implode(', ', $skipped) . ' dilewati karena sudah memiliki data.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function list_bipot()
    {
        return response()->json([
            'bipot' => Bipot::get(),
            'status_mahasiswa' => StatusMahasiswa::get(),
            'status_awal' => StatusMasukMahasiswa::get(),
        ]);
    }
}
