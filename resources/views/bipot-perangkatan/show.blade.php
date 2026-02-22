@extends('layouts.app')
@section('content')
    {{-- @dd($bipot) --}}
    @foreach ($bipot as $t => $item)
        <div class="card">
            <div class="card-header d-flex align-items-center bg-success">
                <h6 class="mb-0">Tahun Angkatan {{ $t }}</h6>
            </div>
            @foreach ($item as $a => $b)
                <div class="card-header d-flex align-items-center bg-info">
                    <h6 class="mb-0">Kelas {{ $a }}</h6>
                </div>
                @foreach ($b as $c => $d)
                    <div class="card-header d-flex align-items-center bg-warning">
                        <h6 class="mb-0">Semester {{ $c }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama BIPOT</th>
                                        <th>Nominal</th>
                                        <th>Status Mahasiswa</th>
                                        <th>Status Awal Mahasiswa</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $total_nominal = 0;
                                    @endphp
                                    @foreach ($d as $e)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $e['nama_bipot'] }}</td>
                                            <td class="text-end">
                                                Rp {{ number_format($e['nominal'], 0, ',', '.') }}
                                            </td>
                                            <td>
                                                @foreach ($e['status_mahasiswa'] as $f)
                                                    <span class="badge bg-info">
                                                        {{ $f }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach ($e['jenis_masuk'] as $f)
                                                    <span class="badge bg-info">
                                                        {{ $f }}
                                                    </span>
                                                @endforeach
                                            </td>

                                        </tr>
                                        @php
                                            $total_nominal += $e['nominal'];
                                        @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">
                                            Total Tagihan
                                        </td>
                                        <td class="text-end fw-bold">
                                            Rp {{ number_format($total_nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endforeach

        </div>
    @endforeach
@endsection
@push('css')
    <link href="{{ asset('') }}assets/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
@endpush
@push('js')
    <script src="{{ asset('') }}assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('') }}assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#mahasiswaTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: false,
            });
        });
    </script>
@endpush
