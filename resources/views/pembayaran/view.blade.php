@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h6 class="mb-0">Data Pembayaran Mahasiswa</h6>
            <div class="ms-auto">
            </div>
        </div>
        <div class="card-body">
            {{-- @dd($pembayaran) --}}
            <div class="table-responsive">
                @php
                    $firstTahun = $pembayaran->first();
                    $firstNpm = $firstTahun ? collect($firstTahun)->first() : null;
                    $firstDetail = $firstNpm['detail'] ?? [];
                @endphp
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
