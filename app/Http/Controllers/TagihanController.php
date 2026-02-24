<?php

namespace App\Http\Controllers;

use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TagihanController extends Controller
{
    private $modul = 'tagihan';
    public function __construct()
    {
        view()->share('modul', $this->modul);
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::connection('db_payment')->table('tagihan');
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
    public function create(Request $request, DataService $service)
    {
        $tipe = $request->query('t');
        abort_unless(in_array($tipe, ['manual', 'otomatis']), 404);
        if ($tipe === 'otomatis') {
            abort_unless($request->ajax(), 404);
            $search = $request->q;
            $data = DB::connection('db_siade')
                ->table('master_mahasiswa')
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('npm', 'like', "%{$search}%")
                            ->orWhere('nama_mahasiswa', 'like', "%{$search}%");
                    });
                })
                ->limit(20)
                ->get();

            $result = $data->map(function ($row) {
                return [
                    'id'   => $row->npm,
                    'text' => e($row->npm . ' - ' . $row->nama_mahasiswa)
                ];
            });

            return response()->json($result);
        } else {
            $d['kelas'] = DB::connection('db_siade')
                ->table('master_kelas_perkuliahan')->get();
            $d['tahun_akademik'] = DB::connection('db_siade')->table('master_tahun_akademik')
                ->orderBy('kode_tahun_akademik', 'desc')
                ->get();
            $d['program_studi'] = DB::connection('db_siade')->table('master_program_studi')->orderBy('nama_program_studi_idn')->get();
            $d['masterBipot'] = DB::table('master_bipot')->get();
            return view('tagihan.create', $d);
        }
    }
    public function store(Request $request, DataService $service)
    {
        $tipe = $request->query('t');
        abort_unless(in_array($tipe, ['manual', 'otomatis']), 404);

        $npm = $request->input('npm');
        $tahun_akademik = $request->input('tahun_akademik');
        if ($tipe == 'otomatis') {
            abort_unless($request->ajax(), 404);
            $response = $service->generateTagihan($npm, $tahun_akademik);
            return response()->json($response);
        } else {
            $request->validate([
                'npm' => 'required|string|max:20',
                'nama_mahasiswa' => 'required|string|max:255',
                'program_studi' => 'required|string|max:20',
                'kelas' => 'required|string|max:50',
                'tahun_akademik' => 'required|string|max:20',
                'jenis_tagihan' => 'required|string|max:50',
                'waktu_berakhir' => 'required|date',
                'detail' => 'required|array|min:1',
                'detail.*.id_bipot' => 'required|integer|exists:master_bipot,id',
                'detail.*.nominal' => 'required|numeric|min:0',
                'nominal_ditagih' => 'required|numeric|min:0'
            ]);

            $ditagihkan = $request->nominal_ditagih;

            $detail = collect($request->detail ?? [])
                ->filter(fn($item) => !empty($item['id_bipot']) && !empty($item['nominal']))
                ->map(function ($item) {
                    return [
                        'id_bipot' => $item['id_bipot'],
                        'nominal'  => (int) $item['nominal'],
                    ];
                })
                ->values();
            $masterBipot = DB::table('master_bipot')
                ->select('id', 'trxid', 'nama_bipot')
                ->get()
                ->keyBy('id');


            $detail_tagihan = $detail->filter(function ($item) use ($masterBipot) {
                return isset($masterBipot[$item['id_bipot']])
                    && $masterBipot[$item['id_bipot']]->trxid == 1;
            })
                ->map(function ($item) use ($masterBipot) {
                    return [
                        'id_bipot'   => $item['id_bipot'],
                        'nama_bipot' => $masterBipot[$item['id_bipot']]->nama_bipot,
                        'nominal'    => $item['nominal'],
                    ];
                })
                ->values()
                ->toArray();

            $detail_potongan = $detail->filter(function ($item) use ($masterBipot) {
                return isset($masterBipot[$item['id_bipot']])
                    && $masterBipot[$item['id_bipot']]->trxid == -1;
            })
                ->map(function ($item) use ($masterBipot) {
                    return [
                        'id_bipot'   => $item['id_bipot'],
                        'nama_bipot' => $masterBipot[$item['id_bipot']]->nama_bipot,
                        'nominal'    => $item['nominal'],
                    ];
                })
                ->values()
                ->toArray();

            $total_tagihan  = collect($detail_tagihan)->sum('nominal');
            $total_potongan = collect($detail_potongan)->sum('nominal');
            $nominal_ditagih = $total_tagihan - $total_potongan;

            if ($nominal_ditagih < 0) {
                return back()->withInput()->with('error', 'Total tagihan tidak boleh minus');
            }
            if ($ditagihkan > $nominal_ditagih) {
                return back()->withInput()->with('error', 'Jumlah ditagihkan tidak boleh lebih besar dari total tagihan');
            }

            $va_code = $service->generateVA($request->tahun_akademik);

            $insert = [
                'id_record_tagihan' => now()->format('YmdHisv') . rand(100, 999),
                'npm' => $npm,
                'nama_mahasiswa' => $request->nama_mahasiswa,
                'nomor_tagihan' => $va_code,
                'id_kelas_perkuliahan' => $request->kelas,
                'nama_kelas_perkuliahan' => $service->kelas($request->kelas)->value('nama_program_perkuliahan'),
                'nama_fakultas' => $service->prodi($request->program_studi)->value('nama_fakultas_idn'),
                'kode_program_studi' => $request->program_studi,
                'nama_program_studi' => $service->prodi($request->program_studi)->value('nama_program_studi_idn'),
                'tahun_akademik' => $request->tahun_akademik,
                'detail_tagihan' => json_encode($detail_tagihan),
                'total_tagihan' => $total_tagihan,
                'nominal_ditagih' => $ditagihkan,
                'detail_potongan' => json_encode($detail_potongan),
                'total_potongan' => $total_potongan,
                'waktu_berakhir' => Carbon::now()->addMonths(2)->endOfDay(),
                'jenis_tagihan' => $request->jenis_tagihan,
            ];
            DB::connection('db_payment')->table('tagihan')->insert($insert);
            return redirect()->route('tagihan.index')->with('success', 'Tagihan berhasil diubah.');
        }
    }
    public function edit($id)
    {
        $id = Crypt::decrypt($id);

        $tagihan = DB::connection('db_payment')
            ->table('tagihan')
            ->where('id', $id)
            ->first();

        if (!$tagihan) {
            abort(404);
        }

        $detail_tagihan  = json_decode($tagihan->detail_tagihan, true) ?? [];
        $detail_potongan = json_decode($tagihan->detail_potongan, true) ?? [];

        $detail = array_merge($detail_tagihan, $detail_potongan);

        $masterBipot = DB::table('master_bipot')->get();

        return view('tagihan.edit', compact(
            'tagihan',
            'detail',
            'masterBipot'
        ));
    }
    public function update(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ID tidak valid'
            ], 400);
        }

        $request->validate([
            'detail.*.id_bipot' => 'required|integer',
            'detail.*.nominal'  => 'required|numeric|min:0',
            'nominal_ditagih'  => 'required|numeric|min:0',
            'waktu_berakhir'    => 'required|date'
        ]);

        $ditagihkan = $request->nominal_ditagih;
        $detail = collect($request->detail ?? [])
            ->filter(fn($item) => !empty($item['id_bipot']) && !empty($item['nominal']))
            ->map(function ($item) {
                return [
                    'id_bipot' => $item['id_bipot'],
                    'nominal'  => (int) $item['nominal'],
                ];
            })
            ->values();
        $masterBipot = DB::table('master_bipot')
            ->select('id', 'trxid', 'nama_bipot')
            ->get()
            ->keyBy('id');


        $detail_tagihan = $detail->filter(function ($item) use ($masterBipot) {
            return isset($masterBipot[$item['id_bipot']])
                && $masterBipot[$item['id_bipot']]->trxid == 1;
        })
            ->map(function ($item) use ($masterBipot) {
                return [
                    'id_bipot'   => $item['id_bipot'],
                    'nama_bipot' => $masterBipot[$item['id_bipot']]->nama_bipot,
                    'nominal'    => $item['nominal'],
                ];
            })
            ->values()
            ->toArray();

        $detail_potongan = $detail->filter(function ($item) use ($masterBipot) {
            return isset($masterBipot[$item['id_bipot']])
                && $masterBipot[$item['id_bipot']]->trxid == -1;
        })
            ->map(function ($item) use ($masterBipot) {
                return [
                    'id_bipot'   => $item['id_bipot'],
                    'nama_bipot' => $masterBipot[$item['id_bipot']]->nama_bipot,
                    'nominal'    => $item['nominal'],
                ];
            })
            ->values()
            ->toArray();

        $total_tagihan  = collect($detail_tagihan)->sum('nominal');
        $total_potongan = collect($detail_potongan)->sum('nominal');
        $nominal_ditagih = $total_tagihan - $total_potongan;

        if ($nominal_ditagih < 0) {
            return back()->withInput()->with('error', 'Total tagihan tidak boleh minus');
        }
        if ($ditagihkan > $nominal_ditagih) {
            return back()->withInput()->with('error', 'Jumlah ditagihkan tidak boleh lebih besar dari total tagihan');
        }

        DB::connection('db_payment')
            ->table('tagihan')
            ->where('id', $id)
            ->update([
                'waktu_berakhir'   => Carbon::parse($request->waktu_berakhir)->endOfDay(),
                'detail_tagihan'   => json_encode($detail_tagihan),
                'detail_potongan'  => json_encode($detail_potongan),
                'total_tagihan'    => $total_tagihan,
                'total_potongan'   => $total_potongan,
                'nominal_ditagih'  => $ditagihkan,
            ]);

        return redirect()->route('tagihan.index')->with('success', 'Tagihan berhasil diubah.');
    }

    public function show($id)
    {
        $id = Crypt::decrypt($id);

        $tagihan = DB::connection('db_payment')
            ->table('tagihan')
            ->where('id', $id)
            ->first();

        if (!$tagihan) {
            abort(404);
        }

        $detail_pembayaran = DB::table('tbl_pembayaran_mahasiswa')
            ->select(
                'id_bipot',
                'nama_bipot',
                DB::raw('SUM(nominal) as nominal'),
            )
            ->where('id_record_tagihan', $tagihan->id_record_tagihan)
            ->groupBy('id_bipot', 'nama_bipot')
            ->get()
            ->keyBy('id_bipot');


        $detail_tagihan  = json_decode($tagihan->detail_tagihan, true) ?? [];
        $detail_tagihan = array_map(function ($item) use ($detail_pembayaran) {
            if (empty($item['dibayar'])) {
                $item['dibayar'] = 0;
            }

            if (isset($detail_pembayaran[$item['id_bipot']])) {
                $item['dibayar'] = rtrim(rtrim($detail_pembayaran[$item['id_bipot']]->nominal, '0'), '.');
            }

            return $item;
        }, $detail_tagihan);

        $detail_potongan = json_decode($tagihan->detail_potongan, true) ?? [];

        $detail = array_merge($detail_tagihan, $detail_potongan);
        return view('tagihan.show', compact(
            'tagihan',
            'detail',
        ));
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $cek = DB::connection('db_payment')
            ->table('tagihan')
            ->where('id', $id)
            ->delete();

        if (!$cek) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dihapus'
        ]);
    }
}
