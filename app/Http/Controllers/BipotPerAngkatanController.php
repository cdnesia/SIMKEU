<?php

namespace App\Http\Controllers;

use App\Models\BipotPerAngkatan;
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DataService $service, $id)
    {
        $id = Crypt::decrypt($id);
        $bipot = $service->bipot();

        $d['bipot'] = $bipot[$id];
        return view('bipot-perangkatan.show', $d);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BipotPerAngkatan $bipotPerAngkatan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BipotPerAngkatan $bipotPerAngkatan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BipotPerAngkatan $bipotPerAngkatan)
    {
        //
    }
}
