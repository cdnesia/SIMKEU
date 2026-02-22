@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h6 class="mb-0">Data Biaya dan Potongan</h6>
            <div class="ms-auto">
                @can($modul . '.create')
                    <a href="{{ route($modul . '.create') }}" class="btn btn-sm btn-primary">Tambah Biaya dan Potongan</a>
                @endcan
                @can($modul . '.sync')
                    <form action="{{ route($modul . '.sync') }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning"><i class="bx bx-sync mr-1"></i>Sync
                            Mahasiswa</button>
                    </form>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="mahasiswaTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Program Studi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prodi as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_program_studi_idn }}</td>
                                <td>
                                    <a
                                        href="{{ route($modul . '.show', Crypt::encrypt($item->kode_program_studi)) }}">Tampilkan</a>
                                </td>
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
                searching: false,
                info: false,
                ordering: false,
            });
        });
    </script>
@endpush
