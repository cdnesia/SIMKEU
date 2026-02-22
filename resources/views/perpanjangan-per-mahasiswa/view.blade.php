@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h6 class="mb-0">Perpanjangan Per Mahasiswa</h6>
            <div class="ms-auto">
                @can($modul . '.create')
                    <a href="{{ route($modul . '.create') }}" class="btn btn-sm btn-primary">Tambah Perpanjangan</a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="mahasiswaTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Mahasiswa</th>
                            <th>Tahun Akademik</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            @canany([$modul . '.destroy', $modul . '.edit'])
                                <th>Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwal_pembayaran as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_mahasiswa }}</td>
                                <td>{{ $item->tanggal_mulai }}</td>
                                <td>{{ $item->tanggal_selesai }}</td>
                                <td>
                                    @foreach (json_decode($item->tahun_akademik, true) as $val)
                                        <small class="d-block">
                                            {{ $val }}
                                        </small>
                                    @endforeach
                                </td>
                                @canany([$modul . '.destroy', $modul . '.edit'])
                                    <td>
                                        @can($modul . '.edit')
                                            <a href="{{ route($modul . '.edit', Crypt::encrypt($item->id)) }}" class="btn btn-warning btn-sm"><i
                                                    class='bx bx-message-square-edit me-0'></i></a>
                                        @endcan
                                        @can($modul . '.destroy')
                                            <form action="{{ route($modul . '.destroy', Crypt::encrypt($item->id)) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Yakin ingin menghapus ini?')">
                                                    <i class='bx bx-message-square-x me-0'></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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
                searching: true,
                info: false,
                ordering: false,
            });
        });
    </script>
@endpush
