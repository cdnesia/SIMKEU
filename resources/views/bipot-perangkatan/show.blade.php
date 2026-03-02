@extends('layouts.app')
@section('content')
    {{-- <div class="card">
        <div class="card-header d-flex align-items-center">
            <h6 class="mb-0">Tahun Angkatan</h6>
        </div>
    </div> --}}
    @foreach ($bipot as $t => $item)
        <div class="card">
            <div class="card-header d-flex align-items-center bg-success">
                <h6 class="mb-0">Tahun Angkatan {{ $t }}</h6>
            </div>
            @foreach ($item as $a => $b)
                @php
                    $parts = explode('-', $a);
                @endphp
                <div class="card-header d-flex align-items-center bg-info">
                    <h6 class="mb-0">Kelas {{ $parts[1] ?? 'Tanpa Program' }}</h6>
                </div>
                @foreach ($b as $c => $d)
                    <div class="card-header d-flex align-items-center bg-warning">
                        <h6 class="mb-0">Semester {{ $c }}</h6>
                        <div class="ms-auto">
                            <button data-kode-tahun="{{ $t }}" data-kelas-id="{{ $parts[0] }}"
                                data-semester="{{ $c }}" class="btn btn-sm btn-success btnAdd"><i
                                    class="bx bx-comment-add me-0"></i></button>
                        </div>
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
                                        <th style="width: 80px">Aksi</th>
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
                                            <td>
                                                <button data-kode-tahun="{{ $t }}"
                                                    data-kelas-id="{{ $a }}"
                                                    data-semester="{{ $c }}"
                                                    data-tagihan-id="{{ $e['id'] }}"
                                                    class="btn btn-sm btn-warning btnEdit"><i
                                                        class="bx bx-comment-edit me-0"></i></button>
                                                <button data-tagihan-id="{{ $e['id'] }}"
                                                    class="btn btn-sm btn-danger btnHapus"><i
                                                        class="bx bx-comment-x me-0"></i></button>
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
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @endforeach
    <!-- Modal Add/Edit -->
    <div class="modal fade" id="modalBipot" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formBipot" class="row g-3">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah BIPOT</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id" id="bipot_id">
                        <input type="hidden" name="kode_tahun" id="kode_tahun">
                        <input type="hidden" name="kelas_id" id="kelas_id">
                        <input type="hidden" name="semester" id="semester">
                        <input type="hidden" name="kode_prodi" id="kode_prodi" value="{{ request()->segment(2) }}">

                        <div class="col-md-12">
                            <label>Nama BIPOT</label>
                            <select name="id_bipot" id="select_bipot" class="form-select select2" required
                                data-placeholder="-- Pilih BIPOT --">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label>Nominal</label>
                            <input type="number" name="nominal" id="nominal" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Status Mahasiswa</label>
                            <div id="checkbox_status_mahasiswa"></div>
                        </div>

                        <div class="mb-3">
                            <label>Status Awal Mahasiswa</label>
                            <div id="checkbox_status_awal"></div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>

                </form>
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

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            function loadMasterData(selectedStatus = [], selectedAwal = [], selectedBipot = null) {
                $.get('/bipot/list-bipot', function(res) {
                    $('#select_bipot').empty().append('<option value="">-- Pilih BIPOT --</option>');

                    $.each(res.bipot, function(i, item) {
                        $('#select_bipot').append(
                            `<option value="${item.id}">${item.nama_bipot}</option>`
                        );
                    });

                    if (selectedBipot) {
                        $('#select_bipot').val(selectedBipot);
                    }

                    $('#checkbox_status_mahasiswa').empty();

                    $.each(res.status_mahasiswa, function(i, item) {

                        let checked = selectedStatus.includes(item.id) ? 'checked' : '';

                        $('#checkbox_status_mahasiswa').append(`
                <div class="form-check">
                    <input class="form-check-input status-mahasiswa"
                        type="checkbox"
                        value="${item.id}"
                        name="status_mahasiswa[]"
                        ${checked}>
                    <label class="form-check-label">${item.nama_status_mahasiswa}</label>
                </div>
            `);
                    });

                    $('#checkbox_status_awal').empty();

                    $.each(res.status_awal, function(i, item) {

                        let checked = selectedAwal.includes(item.id) ? 'checked' : '';

                        $('#checkbox_status_awal').append(`
                <div class="form-check">
                    <input class="form-check-input status-awal"
                        type="checkbox"
                        value="${item.id}"
                        name="status_awal[]"
                        ${checked}>
                    <label class="form-check-label">${item.nama_jenis_pendaftaran}</label>
                </div>
            `);
                    });
                });
            }

            $(document).on('click', '.btnAdd', function() {

                $('#modalTitle').text('Tambah BIPOT');
                $('#formBipot')[0].reset();
                $('#bipot_id').val('');

                $('#kode_tahun').val($(this).data('kode-tahun'));
                $('#kelas_id').val($(this).data('kelas-id'));
                $('#semester').val($(this).data('semester'));

                loadMasterData();

                $('#modalBipot').modal('show');
            });

            $('#formBipot').submit(function(e) {
                e.preventDefault();

                let id = $('#bipot_id').val();
                let url, type;
                if (id) {
                    url = '/bipot-per-angkatan/' + id;
                    type = 'PUT';
                } else {
                    url = '/bipot-per-angkatan';
                    type = 'POST';
                }

                $.ajax({
                    url: url,
                    type: type,
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            $('#modalBipot').modal('hide');
                            alert(res.message);
                            location.reload();
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Terjadi kesalahan sistem.');
                    }
                });
            });

            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('tagihan-id');
                $('#modalTitle').text('Edit BIPOT');
                $('#bipot_id').val(id);

                $.get('/bipot-per-angkatan/' + id + '/edit', function(data) {
                    $('#nominal').val(data.nominal);
                    loadMasterData(data.status_mahasiswa ?? [], data.status_awal ?? [], data
                        .id_bipot);
                    $('#modalBipot').modal('show');
                });
            });

            $(document).on('click', '.btnHapus', function() {

                let id = $(this).data('tagihan-id');

                if (!confirm('Yakin ingin menghapus data ini?')) return;

                $.ajax({
                    url: '/bipot-per-angkatan/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            alert(res.message);
                            location.reload();
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Terjadi kesalahan sistem.');
                    }
                });

            });
        });
    </script>
@endpush
