<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PerpanjanganPerMahasiswaController extends Controller
{
    private $modul = 'perpanjangan-per-mahasiswa';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }
    public function index()
    {
        $master_mahasiswa = DB::connection('db_siade')->table('master_mahasiswa')->get()->keyBy('npm');

        $jadwal = DB::table('master_jadwal_perpanjangan')
            ->orderBy('npm', 'DESC')
            ->get()
            ->map(function ($item) use ($master_mahasiswa) {
                $mahasiswa = $master_mahasiswa[$item->npm] ?? null;
                $item->nama_mahasiswa = $mahasiswa->nama_mahasiswa ?? null;
                return $item;
            });

        $d['jadwal_pembayaran'] = $jadwal;
        return view($this->modul . '.view', $d);
    }
    public function create()
    {
        $master_mahasiswa = DB::connection('db_siade')->table('master_mahasiswa')->get()->keyBy('npm');
        $d['tahun_akademik'] = DB::connection('db_siade')->table('master_tahun_akademik')->orderBy('kode_tahun_akademik', 'DESC')->get();
        $d['mahasiswa'] = $master_mahasiswa;
        $d['data'] = null;
        return view($this->modul . '.form', $d);
    }
    public function store(Request $request)
    {
        $request->validate([
            'npm'                 => 'required',
            'tahun_akademik'      => 'required|array',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        DB::table('master_jadwal_perpanjangan')->insert([
            'tahun_akademik'      => json_encode($request->tahun_akademik),
            'tanggal_mulai'       => $request->tanggal_mulai,
            'tanggal_selesai'     => $request->tanggal_selesai,
            'npm'  => $request->npm,
        ]);

        return redirect()
            ->route($this->modul . '.index')
            ->with('success', 'Data berhasil disimpan');
    }
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $master_mahasiswa = DB::connection('db_siade')->table('master_mahasiswa')->get()->keyBy('npm');
        $d['tahun_akademik'] = DB::connection('db_siade')->table('master_tahun_akademik')->orderBy('kode_tahun_akademik', 'DESC')->get();
        $d['mahasiswa'] = $master_mahasiswa;
        $d['data'] = DB::table('master_jadwal_perpanjangan')
            ->where('id', $id)
            ->first();

        if (!$d['data']) {
            abort(404);
        }
        $d['data']->tahun_akademik = json_decode($d['data']->tahun_akademik, true) ?? [];
        if (isset($d['data']->tanggal_mulai)) {
            $d['data']->tanggal_mulai = Carbon::parse($d['data']->tanggal_mulai)
                ->format('Y-m-d');
        }
        if (isset($d['data']->tanggal_selesai)) {
            $d['data']->tanggal_selesai = Carbon::parse($d['data']->tanggal_selesai)
                ->format('Y-m-d');
        }

        return view($this->modul . '.form', $d);
    }
    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $request->validate([
            'npm'                 => 'required',
            'tahun_akademik'      => 'required|array',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
        ]);


        $data = DB::table('master_jadwal_perpanjangan')
            ->where('id', $id)
            ->first();

        if (!$data) {
            abort(404);
        }

        DB::table('master_jadwal_perpanjangan')
            ->where('id', $id)
            ->update([
                'tahun_akademik'      => json_encode($request->tahun_akademik),
                'tanggal_mulai'       => $request->tanggal_mulai,
                'tanggal_selesai'     => $request->tanggal_selesai,
                'npm'  => $request->npm,
            ]);

        return redirect()
            ->route($this->modul.'.index')
            ->with('success', 'Data berhasil diperbarui');
    }
    public function destroy($id)
    {
        try {

            $id = Crypt::decrypt($id);

            $data = DB::table('master_jadwal_perpanjangan')
                ->where('id', $id)
                ->first();

            if (!$data) {
                abort(404);
            }

            DB::beginTransaction();

            DB::table('master_jadwal_perpanjangan')
                ->where('id', $id)
                ->delete();

            DB::commit();

            return redirect()
                ->route($this->modul . '.index')
                ->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->with('error', 'Gagal menghapus data');
        }
    }
}
