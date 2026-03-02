<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TagihanPMBController extends Controller
{
    private $modul = 'tagihan';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::connection('db_payment')->table('tagihan')->where('tahun_akademik','like', '%UMJA%');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('detail_tagihan', function ($item) {
                    $detail = '';
                    $json = json_decode($item->detail_tagihan, true);

                    if (is_array($json)) {
                        foreach ($json as $val) {
                            $detail .= '<span class="d-block">' .
                                ($val['nama_bipot'] ?? '-') . ' : Rp ' .
                                number_format($val['nominal'] ?? 0, 0, ',', '.') .
                                '</span>';
                        }
                    }

                    return $detail;
                })
                ->editColumn('detail_potongan', function ($item) {
                    $detail = '';
                    $json = json_decode($item->detail_potongan, true);

                    if (is_array($json)) {
                        foreach ($json as $val) {
                            $detail .= '<span class="d-block">' .
                                ($val['nama_bipot'] ?? '-') . ' : Rp ' .
                                number_format($val['nominal'] ?? 0, 0, ',', '.') .
                                '</span>';
                        }
                    }

                    return $detail;
                })

                ->editColumn('total_tagihan', function ($item) {
                    return 'Rp ' . number_format($item->total_tagihan, 0, ',', '.');
                })

                ->editColumn('total_potongan', function ($item) {
                    return 'Rp ' . number_format($item->total_potongan, 0, ',', '.');
                })

                ->editColumn('nominal_ditagih', function ($item) {
                    return 'Rp ' . number_format($item->nominal_ditagih, 0, ',', '.');
                })

                ->editColumn('nominal_terbayar', function ($item) {
                    return 'Rp ' . number_format($item->nominal_terbayar, 0, ',', '.');
                })
                ->editColumn('status_aktif', function ($item) {
                    if ($item->status_aktif == 'Y') {
                        $status = "<span class='badge bg-success w-100'>Aktif</span>";
                    } else {
                        $status = "<span class='badge bg-danger w-100'>Tidak Aktif</span>";
                    }
                    return $status;
                })

                ->addColumn('aksi', function ($item) {
                    $id = Crypt::encrypt($item->id);

                    return '
                    <a href="' . route('tagihan.show', $id) . '" class="btn btn-success btn-sm btn-flagging pb-2"><i class="bx bx-flag me-0"></i></a>
                    <a href="' . route('tagihan.edit', $id) . '" class="btn btn-warning btn-sm btn-edit pb-2"><i class="bx bx-message-square-edit me-0"></i></a>
                    <button data-id="' . $id . '" class="btn btn-danger btn-sm btn-delete pb-2"><i class="bx bx-message-square-x me-0"></i></button>
                    ';
                })

                ->rawColumns(['detail_tagihan', 'detail_potongan', 'aksi', 'status_aktif'])
                ->make(true);
        }

        $d['master_tagihan'] = DB::table('master_bipot')
            ->where('trxid', '1')
            ->get();

        $d['master_potongan'] = DB::table('master_bipot')
            ->where('trxid', '-1')
            ->get();

        $d['tahun_akademik'] = DB::connection('db_siade')->table('master_tahun_akademik')
            ->orderBy('kode_tahun_akademik', 'desc')
            ->get();

        return view('tagihan.view', $d);
    }
}
