@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-3">
            <span class="d-inline-flex align-items-center justify-content-center bg-light-primary text-primary rounded-circle flex-shrink-0"
                style="width:48px;height:48px;">
                <i class="bx bx-book-content fs-4"></i>
            </span>
            <div class="me-auto">
                <small class="text-muted text-uppercase">Data Biaya dan Potongan</small>
                <h5 class="mb-0 fw-bold">{{ $prodi_info->nama_program_studi_idn ?? '-' }}</h5>
                @if (!empty($prodi_info->nama_fakultas_idn))
                    <span class="text-muted small">{{ $prodi_info->nama_fakultas_idn }}</span>
                @endif
            </div>
            <a href="{{ route($modul . '.index') }}" class="btn btn-sm btn-warning"><i
                    class="bx bx-arrow-back me-0"></i> Kembali</a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Tahun Angkatan</label>
                    <select name="tahun_akademik" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Tahun Angkatan --</option>
                        @foreach ($tahun_akademik_list as $ta)
                            <option value="{{ $ta->kode_tahun }}"
                                {{ (string) $tahun_akademik_terpilih === (string) $ta->kode_tahun ? 'selected' : '' }}>
                                {{ $ta->nama_tahun }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1">Kelas</label>
                    <select name="kelas" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kelas --</option>
                        @foreach ($kelas_list as $kl)
                            <option value="{{ $kl->id }}"
                                {{ (string) $kelas_terpilih === (string) $kl->id ? 'selected' : '' }}>
                                {{ $kl->nama_program_perkuliahan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1">Semester</label>
                    <select name="semester" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Semester --</option>
                        @foreach ($semester_list as $sm)
                            <option value="{{ $sm }}"
                                {{ (string) $semester_terpilih === (string) $sm ? 'selected' : '' }}>
                                Semester {{ $sm }}
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

    @if (count($bipot) === 0)
        <div class="alert alert-warning">Tidak ada data BIPOT untuk tahun angkatan yang dipilih.</div>
    @endif

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
                                data-semester="{{ $c }}" class="btn btn-sm btn-success btnAdd" title="Tambah BIPOT"><i
                                    class="bx bx-comment-add me-0"></i></button>
                            @if (count($d) > 0)
                                <button data-kode-tahun="{{ $t }}" data-kelas-id="{{ $parts[0] }}"
                                    data-kelas-nama="{{ $parts[1] ?? 'Tanpa Program' }}" data-semester="{{ $c }}"
                                    class="btn btn-sm btn-primary btnCopy" title="Salin ke semester lain"><i
                                        class="bx bx-copy me-0"></i></button>
                            @endif
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
    <div class="modal fade" id="modalBipot" tabindex="-1" aria-labelledby="modalBipotLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalBipotLabel">Tambah BIPOT</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formBipot">
                        @csrf
                        <input type="hidden" name="id" id="bipot_id">
                        <input type="hidden" name="kode_tahun" id="kode_tahun">
                        <input type="hidden" name="kelas_id" id="kelas_id">
                        <input type="hidden" name="semester" id="semester">
                        <input type="hidden" name="kode_prodi" id="kode_prodi" value="{{ request()->segment(2) }}">

                        <div class="mb-2">
                            <label for="select_bipot" class="form-label">Nama BIPOT</label>
                            <select name="id_bipot" id="select_bipot" class="form-select select2" required
                                data-placeholder="-- Pilih BIPOT --">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label for="nominal" class="form-label">Nominal</label>
                            <input type="number" name="nominal" id="nominal" class="form-control" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Status Mahasiswa</label>
                            <div id="checkbox_status_mahasiswa"></div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Status Awal Mahasiswa</label>
                            <div id="checkbox_status_awal"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="formBipot" class="btn btn-success btn-sm">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Copy Semester -->
    <div class="modal fade" id="modalCopyBipot" tabindex="-1" aria-labelledby="modalCopyBipotLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalCopyBipotLabel">Salin Data BIPOT</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCopyBipot">
                        @csrf
                        <input type="hidden" name="kode_tahun" id="copy_kode_tahun">
                        <input type="hidden" name="kelas_id" id="copy_kelas_id">
                        <input type="hidden" name="source_semester" id="copy_source_semester">
                        <input type="hidden" name="kode_prodi" value="{{ request()->segment(2) }}">

                        <div class="mb-2">
                            <p class="mb-1">Salin semua data BIPOT dari <strong id="copy_source_label"></strong> ke:
                            </p>
                        </div>

                        <div class="mb-2">
                            <label for="target_kode_prodi" class="form-label">Program Studi Tujuan</label>
                            <select name="target_kode_prodi" id="target_kode_prodi" class="form-select select2"
                                required data-placeholder="-- Pilih Program Studi --">
                                <option value=""></option>
                                @foreach ($prodi_master as $p)
                                    <option value="{{ $p->kode_program_studi }}">{{ $p->nama_program_studi_idn }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label for="target_kode_tahun" class="form-label">Tahun Angkatan Tujuan</label>
                            <select name="target_kode_tahun" id="target_kode_tahun" class="form-select select2"
                                required data-placeholder="-- Pilih Tahun Angkatan --">
                                <option value=""></option>
                                @foreach ($tahun_akademik_master as $ta)
                                    <option value="{{ $ta->kode_tahun_akademik }}">{{ $ta->nama_tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label for="target_kelas_id" class="form-label">Kelas Tujuan</label>
                            <select name="target_kelas_id" id="target_kelas_id" class="form-select select2" required
                                data-placeholder="-- Pilih Kelas --">
                                <option value=""></option>
                                @foreach ($kelas_master as $kl)
                                    <option value="{{ $kl->id }}">{{ $kl->nama_program_perkuliahan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Semester Tujuan</label>
                            <div id="checkbox_target_semester"></div>
                            <div class="form-text">Semester sumber otomatis disembunyikan jika program studi, tahun
                                akademik, dan kelas tujuan sama persis dengan sumber.</div>
                        </div>

                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="overwrite" value="1"
                                    id="copy_overwrite">
                                <label class="form-check-label" for="copy_overwrite">
                                    Timpa data yang sudah ada di semester tujuan
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="formCopyBipot" class="btn btn-primary btn-sm">Salin</button>
                </div>
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

                $('#modalBipotLabel').text('Tambah BIPOT');
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

            const allSemesters = @json($semester_list);
            const kodeProdiSaatIni = @json($kode_prodi_saat_ini);

            function renderTargetSemesterCheckboxes() {
                let sourceSemester = $('#copy_source_semester').val();
                let isSameAngkatan = String($('#target_kode_prodi').val()) === String(kodeProdiSaatIni) &&
                    String($('#target_kode_tahun').val()) === String($('#copy_kode_tahun').val()) &&
                    String($('#target_kelas_id').val()) === String($('#copy_kelas_id').val());

                $('#checkbox_target_semester').empty();
                $.each(allSemesters, function(i, sm) {
                    if (isSameAngkatan && sm == sourceSemester) return;
                    $('#checkbox_target_semester').append(`
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" value="${sm}"
                        name="target_semester[]" id="target_semester_${sm}">
                    <label class="form-check-label" for="target_semester_${sm}">Semester ${sm}</label>
                </div>
            `);
                });
            }

            $(document).on('change', '#target_kode_prodi, #target_kode_tahun, #target_kelas_id', function() {
                renderTargetSemesterCheckboxes();
            });

            $(document).on('click', '.btnCopy', function() {
                let sourceSemester = $(this).data('semester');
                let kelasNama = $(this).data('kelas-nama');
                let kodeTahun = $(this).data('kode-tahun');
                let kelasId = $(this).data('kelas-id');

                $('#formCopyBipot')[0].reset();
                $('#copy_kode_tahun').val(kodeTahun);
                $('#copy_kelas_id').val(kelasId);
                $('#copy_source_semester').val(sourceSemester);
                $('#copy_source_label').text('Kelas ' + kelasNama + ' - Semester ' + sourceSemester);

                $('#target_kode_prodi').val(kodeProdiSaatIni).trigger('change');
                $('#target_kode_tahun').val(String(kodeTahun)).trigger('change');
                $('#target_kelas_id').val(String(kelasId)).trigger('change');

                renderTargetSemesterCheckboxes();

                $('#modalCopyBipot').modal('show');
            });

            $('#formCopyBipot').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: '{{ route('bipot-per-angkatan.copy-semester') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        alert(res.message);
                        if (res.success) {
                            $('#modalCopyBipot').modal('hide');
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            alert(Object.values(xhr.responseJSON.errors).flat().join('\n'));
                        } else {
                            alert('Terjadi kesalahan sistem.');
                        }
                    }
                });
            });

            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('tagihan-id');
                $('#modalBipotLabel').text('Edit BIPOT');
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
