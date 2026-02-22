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
                            <th>Nama Bipot</th>
                            <th>Jenis</th>
                            <th>Urutan</th>
                            @canany([$modul . '.destroy', $modul . '.edit'])
                                <th>Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bipot as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_bipot }}</td>
                                <td>{{ $item->trxid == '-1' ? 'Potongan' : 'Pembayaran' }}</td>
                                <td>{{ $item->urutan }}</td>
                                @canany([$modul . '.destroy', $modul . '.edit'])
                                    <td>
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
                searching: false,
                info: false,
                ordering: false,
            });
        });
    </script>
@endpush
