@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h6 class="mb-0">Data Tagihan Mahasiswa</h6>
            <div class="ms-auto">
                @can($modul . '.create')
                    <a href="{{ route($modul . '.create') }}?t=manual" class="btn btn-sm btn-primary">Tambah Manual</a>
                    <a href="javascript::void(0)" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#addTagihan">Tambah Otomatis</a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th width="30px">No</th>
                            <th>Nomor Tagihan</th>
                            <th>Nomor Pokok Mahasiswa</th>
                            <th>Nama Mahasiswa</th>
                            <th>Fakultas</th>
                            <th>Program Studi</th>
                            <th>Kelas Perkuliahan</th>
                            <th>Tahun Akademik</th>
                            <th>Waktu Kadaluarsa</th>
                            <th>Detail Tagihan</th>
                            <th>Total Tagihan</th>
                            <th>Detail Potongan</th>
                            <th>Total Potongan</th>
                            <th>Jumlah Ditagih</th>
                            <th>Jumlah Terbayar</th>
                            <th>Status Tagihan</th>
                            @canany([$modul . '.edit', $modul . '.destroy'])
                                <th width="50px">Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addTagihan" tabindex="-1" aria-labelledby="addTagihanLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="addTagihanLabel">Generate Tagihan Mahasiswa</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-tagihan" action="{{ route('tagihan.store') }}?t=otomatis" method="post">
                        @csrf
                        <div class="mb-2">
                            <label for="npm" class="form-label">Nama Mahasiswa</label>
                            <select class="form-select" id="npm" data-placeholder="-- Pilih Mahasiswa --"
                                name="npm">
                            </select>
                        </div>
                        <div class="mb-2">
                            <label for="tahun_akademik" class="form-label">Tahun Akademik</label>
                            <select class="form-select select2" id="tahun_akademik"
                                data-placeholder="-- Pilih Tahun Akademik --" name="tahun_akademik">
                                <option value=""></option>
                                @foreach ($tahun_akademik as $item)
                                    <option value="{{ $item->kode_tahun_akademik }}">{{ $item->nama_tahun_akademik }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="btn-save" class="btn btn-success btn-sm">Simpan</button>
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
        let table;
        let masterTagihan = @json($master_tagihan ?? []);
        let masterPotongan = @json($master_potongan ?? []);
        $(document).ready(function() {
            table = $('#example').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: "{{ url()->current() }}",
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nomor_tagihan',
                        name: 'nomor_tagihan'
                    },
                    {
                        data: 'npm',
                        name: 'npm'
                    },
                    {
                        data: 'nama_mahasiswa',
                        name: 'nama_mahasiswa'
                    },
                    {
                        data: 'nama_fakultas',
                        name: 'nama_fakultas'
                    },
                    {
                        data: 'nama_program_studi',
                        name: 'nama_program_studi'
                    },
                    {
                        data: 'nama_kelas_perkuliahan',
                        name: 'nama_kelas_perkuliahan'
                    },
                    {
                        data: 'tahun_akademik',
                        name: 'tahun_akademik'
                    },
                    {
                        data: 'waktu_berakhir',
                        name: 'waktu_berakhir'
                    },
                    {
                        data: 'detail_tagihan',
                        name: 'detail_tagihan',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'total_tagihan',
                        name: 'total_tagihan'
                    },
                    {
                        data: 'detail_potongan',
                        name: 'detail_potongan',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'total_potongan',
                        name: 'total_potongan'
                    },
                    {
                        data: 'nominal_ditagih',
                        name: 'nominal_ditagih'
                    },
                    {
                        data: 'nominal_terbayar',
                        name: 'nominal_terbayar'
                    },
                    {
                        data: 'status_aktif',
                        name: 'status_aktif'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $(document).on('click', '.btn-delete', function() {
                let id = $(this).data('id');
                let button = $(this);

                if (!confirm('Yakin ingin menghapus data ini?')) {
                    return;
                }

                $.ajax({
                    url: '/tagihan/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Lobibox.notify('success', {
                                pauseDelayOnHover: true,
                                size: 'mini',
                                rounded: true,
                                icon: 'bx bx-check-circle',
                                delayIndicator: false,
                                continueDelayOnInactiveTab: false,
                                position: 'top right',
                                msg: response.message,
                                sound: false,
                            });

                            table.ajax.reload(null, false);
                        } else {
                            Lobibox.notify('error', {
                                pauseDelayOnHover: true,
                                size: 'mini',
                                rounded: true,
                                icon: 'bx bx-x-circle',
                                delayIndicator: false,
                                continueDelayOnInactiveTab: false,
                                position: 'top right',
                                msg: response.message,
                                sound: false,
                            });
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan server';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message
                            alert(msg);

                        }
                    }
                });
            });

            $('#addTagihan').on('shown.bs.modal', function() {
                $('#npm').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    allowClear: true,
                    placeholder: '-- Pilih Mahasiswa --',
                    dropdownParent: $('#addTagihan'),

                    ajax: {
                        url: "{{ route('tagihan.create') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                t: 'otomatis'
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    }
                });

            });

            $(document).on('click', '#btn-save', function() {

                let button = $(this);
                let form = $('#form-tagihan');
                let url = form.attr('action');
                let formData = form.serialize();

                button.prop('disabled', true).text('Menyimpan...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        console.log(response);

                        if (response.success) {
                            Lobibox.notify('success', {
                                pauseDelayOnHover: true,
                                size: 'mini',
                                rounded: true,
                                icon: 'bx bx-check-circle',
                                delayIndicator: false,
                                continueDelayOnInactiveTab: false,
                                position: 'top right',
                                msg: response.message,
                                sound: false,
                            });

                            form[0].reset();
                            $('#npm').val(null).trigger('change');

                            $('#addTagihan').modal('hide');

                            table.ajax.reload(null, false);

                        } else {
                            Lobibox.notify('error', {
                                pauseDelayOnHover: true,
                                size: 'mini',
                                rounded: true,
                                icon: 'bx bx-x-circle',
                                delayIndicator: false,
                                continueDelayOnInactiveTab: false,
                                position: 'top right',
                                msg: response.message,
                                sound: false,
                            });
                        }
                    },
                    error: function(xhr) {

                        let msg = 'Terjadi kesalahan server';

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)[0][0];
                        }

                        Lobibox.notify('error', {
                            size: 'mini',
                            rounded: true,
                            position: 'top right',
                            msg: msg,
                            sound: false,
                        });
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Simpan');
                    }
                });

            });
        });
    </script>
@endpush
