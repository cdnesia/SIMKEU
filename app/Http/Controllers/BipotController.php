<?php

namespace App\Http\Controllers;

use App\Models\Bipot;
use Illuminate\Http\Request;

class BipotController extends Controller
{
    private $modul = 'bipot';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $d['bipot'] = Bipot::orderBy('trxid')->orderBy('nama_bipot')->get();
        return view('bipot.view', $d);
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
    public function show(Bipot $bipot)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bipot $bipot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bipot $bipot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bipot $bipot)
    {
        //
    }
}
