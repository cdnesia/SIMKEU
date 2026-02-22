<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class JadwalPembayaranController extends Controller
{
    private $modul = 'jadwal-pembayaran';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }
    public function index()
    {
        $master_program_studi = DB::connection('db_siade')->table('master_program_studi')->get()->keyBy('kode_program_studi');

        $jadwal = DB::table('master_jadwal_pembayaran')
            ->orderBy('tahun_akademik', 'DESC')
            ->get();

        $jadwal = $jadwal->map(function ($item) use ($master_program_studi) {

            $prodiIds = json_decode($item->kode_program_studi, true) ?? [];

            $item->program_studi_detail = collect($prodiIds)
                ->map(function ($kode) use ($master_program_studi) {
                    return $master_program_studi[$kode] ?? null;
                })
                ->filter()
                ->values();
            return $item;
        });

        $d['jadwal_pembayaran'] = $jadwal;
        return view($this->modul . '.view', $d);
    }
    public function create()
    {
        $master_program_studi = DB::connection('db_siade')->table('master_program_studi')->orderBy('nama_program_studi_idn')->get()->keyBy('kode_program_studi');
        $d['tahun_akademik'] = DB::connection('db_siade')->table('master_tahun_akademik')->orderBy('kode_tahun_akademik', 'DESC')->get();
        $d['program_studi'] = $master_program_studi;
        $d['data'] = null;
        return view($this->modul . '.form', $d);
    }
    public function store(Request $request)
    {
        $request->validate([
            'tahun_akademik'      => 'required',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
            'kode_program_studi'  => 'required|array',
        ]);

        DB::table('master_jadwal_pembayaran')->insert([
            'tahun_akademik'      => $request->tahun_akademik,
            'tanggal_mulai'       => $request->tanggal_mulai,
            'tanggal_selesai'     => $request->tanggal_selesai,
            'kode_program_studi'  => json_encode($request->kode_program_studi),
        ]);

        return redirect()
            ->route($this->modul . '.index')
            ->with('success', 'Data berhasil disimpan');
    }
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $master_program_studi = DB::connection('db_siade')->table('master_program_studi')->orderBy('nama_program_studi_idn')->get()->keyBy('kode_program_studi');
        $d['tahun_akademik'] = DB::connection('db_siade')->table('master_tahun_akademik')->orderBy('kode_tahun_akademik', 'DESC')->get();
        $d['program_studi'] = $master_program_studi;
        $d['data'] = DB::table('master_jadwal_pembayaran')
            ->where('id', $id)
            ->first();

        if (!$d['data']) {
            abort(404);
        }
        $d['data']->kode_program_studi = json_decode($d['data']->kode_program_studi, true) ?? [];
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
            'tahun_akademik'      => 'required',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
            'kode_program_studi'  => 'required|array',
        ]);

        $data = DB::table('master_jadwal_pembayaran')
            ->where('id', $id)
            ->first();

        if (!$data) {
            abort(404);
        }

        DB::table('master_jadwal_pembayaran')
            ->where('id', $id)
            ->update([
                'tahun_akademik'     => $request->tahun_akademik,
                'tanggal_mulai'      => $request->tanggal_mulai,
                'tanggal_selesai'    => $request->tanggal_selesai,
                'kode_program_studi' => json_encode($request->kode_program_studi),
            ]);

        return redirect()
            ->route('jadwal-pembayaran.index')
            ->with('success', 'Data berhasil diperbarui');
    }
    public function destroy($id)
    {
        try {

            $id = Crypt::decrypt($id);

            $data = DB::table('master_jadwal_pembayaran')
                ->where('id', $id)
                ->first();

            if (!$data) {
                abort(404);
            }

            DB::beginTransaction();

            DB::table('master_jadwal_pembayaran')
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
