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

    public function list_bipot()
    {
        return response()->json([
            'bipot' => Bipot::get(),
            'status_mahasiswa' => StatusMahasiswa::get(),
            'status_awal' => StatusMasukMahasiswa::get(),
        ]);
    }
}
