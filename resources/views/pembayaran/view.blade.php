@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-3">
            <span class="d-inline-flex align-items-center justify-content-center bg-light-primary text-primary rounded-circle flex-shrink-0"
                style="width:48px;height:48px;">
                <i class="bx bx-wallet fs-4"></i>
            </span>
            <div class="me-auto">
                <small class="text-muted text-uppercase">Data Pembayaran</small>
                <h5 class="mb-0 fw-bold">Pembayaran Mahasiswa</h5>
                <span class="text-muted small">Rekap pembayaran per tahun akademik dan BIPOT</span>
            </div>
            <a href="{{ route('pembayaran.export', request()->query()) }}" class="btn btn-sm btn-success"><i
                    class="bx bx-file-blank me-0"></i> Export Excel</a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Tahun Akademik</label>
                    <select name="tahun_akademik" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Tahun Akademik --</option>
                        @foreach ($tahun_akademik_list as $ta)
                            <option value="{{ $ta }}"
                                {{ (string) $tahun_akademik_terpilih === (string) $ta ? 'selected' : '' }}>
                                {{ $ta }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1">BIPOT</label>
                    <select name="bipot" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua BIPOT --</option>
                        @foreach ($bipot_list as $b)
                            <option value="{{ $b->id }}"
                                {{ (string) $bipot_terpilih === (string) $b->id ? 'selected' : '' }}>
                                {{ $b->nama_bipot }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-warning"><i
                            class="bx bx-reset me-0"></i> Reset Filter</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th width="30px">No</th>
                            <th>Tahun Akademik</th>
                            <th>Nomor Pokok Mahasiswa</th>
                            <th>Total Pembayaran</th>
                            @foreach ($firstDetail as $detail)
                                <th>{{ $detail['nama_bipot'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($pembayaran as $tahun => $npms)
                            @foreach ($npms as $npm => $val)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $tahun }}</td>
                                    <td>{{ $npm }}</td>
                                    <td class="text-end">{{ number_format($val['total_terbayar'], 0, ',', '.') }}</td>
                                    @foreach ($val['detail'] as $key => $item)
                                        <td class="text-end">{{ number_format($item['nominal'], 0, ',', '.') }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
