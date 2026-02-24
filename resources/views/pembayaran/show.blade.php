@extends('layouts.app')

@section('content')
    <h6 class="text-uppercase">Flag tagihan mahasiswa</h6>
    <hr>
    <form class="row g-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nomor Pokok Mahasiswa</label>
                        <input type="text" class="form-control" value="{{ $tagihan->npm }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Mahasiswa</label>
                        <input type="text" class="form-control" value="{{ $tagihan->nama_mahasiswa }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Program Studi</label>
                        <input type="text" class="form-control" value="{{ $tagihan->nama_program_studi }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fakultas</label>
                        <input type="text" class="form-control" value="{{ $tagihan->nama_fakultas }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <input type="text" class="form-control" value="{{ $tagihan->nama_kelas_perkuliahan }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Akademik</label>
                        <input type="text" class="form-control" value="{{ $tagihan->tahun_akademik }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Tagihan</label>
                        <input type="text" class="form-control" value="{{ $tagihan->jenis_tagihan }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h6 class="mb-0">Detail Biaya dan Potongan</h6>
                    <div class="ms-auto">
                        <a href="{{ route('tagihan.index') }}" class="btn btn-warning btn-sm">Kembali</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-resposive">
                        <table class="table table-bordered" id="tableDetail">
                            <thead>
                                <tr>
                                    <th>Biaya / Potongan</th>
                                    <th width="200">Nominal</th>
                                    <th width="200">Dibayar</th>
                                    <th width="50">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detail as $index => $row)
                                    @php
                                        $safeNominal = preg_replace('/[^0-9]/', '', $row['nominal'] ?? 0);
                                        $safeDibayar = preg_replace('/[^0-9]/', '', $row['dibayar'] ?? 0);
                                        $safeBipot = $row['id_bipot'] ?? '';
                                    @endphp
                                    <tr>
                                        <td>{{ $row['nama_bipot'] }}</td>
                                        <td>
                                            <input type="text" name="detail[{{ $index }}][nominal]"
                                                value="{{ $safeNominal }}" class="form-control nominal" readonly>
                                        </td>
                                        <td>
                                            <input type="text" data-id="{{ $safeBipot }}"
                                                name="detail[{{ $index }}][dibayar]" value="{{ $safeDibayar }}"
                                                class="form-control nominal" inputmode="numeric" required disabled>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-success btn-sm btnSave">
                                                <i class="bx bx-check-circle me-0"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Total Tagihan</label>
                            <input type="text" id="total_tagihan" class="form-control" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jumlah Dibayar</label>
                            <input type="text" id="nominal_dibayar" class="form-control" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h6 class="mb-0">Data Pembayaran</h6>
                </div>
                <div class="card-body">
                    <div class="table-resposive">
                        <table id="tableTagihan" class="table table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th style="width: 30px">No</th>
                                    <th>Nama Pembayaran</th>
                                    <th>Nominal</th>
                                    <th>Keterangan</th>
                                    <th style="width: 50px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('css')
    <link href="{{ asset('') }}assets/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <style>
        tr {
            font-size: 0.8rem !important;
        }
    </style>
@endpush
@push('js')
    <script src="{{ asset('') }}assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('') }}assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
    <script>
        let table;
        $(document).ready(function() {
            let segments = window.location.pathname.split('/');
            let encryptedId = segments.pop() || segments.pop();
            console.log(encryptedId);

            table = $('#tableTagihan').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                info: false,
                paginate: false,
                searching: false,
                ajax: "{{ url('pembayaran') }}/" + encryptedId + "/edit",
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                    emptyTable: "Tidak ada data yang tersedia",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    lengthMenu: "Tampilkan _MENU_ data",
                    loadingRecords: "Memuat...",
                    search: "Cari:",
                    zeroRecords: "Data tidak ditemukan",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_bipot',
                        name: 'nama_bipot'
                    },
                    {
                        data: 'nominal',
                        name: 'nominal'
                    },
                    {
                        data: 'metode',
                        name: 'metode'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                ]
            });


            const token = $('meta[name="csrf-token"]').attr('content');

            function cleanNumber(val) {
                return parseInt(String(val || '').replace(/\D/g, ''), 10) || 0;
            }

            function formatRupiah(val) {
                val = cleanNumber(val);
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            $('.nominal').each(function() {
                let n = $(this).val();
                if (n) $(this).val(formatRupiah(n));
            });

            function hitungTotalTagihan() {
                let total = 0;
                $('#tableDetail tbody tr').each(function() {
                    total += cleanNumber($(this).find('input[name*="[nominal]"]').val());
                });
                $('#total_tagihan').val(formatRupiah(total));
            }

            function hitungTotalDibayar() {
                let total = 0;
                $('#tableDetail tbody tr').each(function() {
                    total += cleanNumber($(this).find('input[name*="[dibayar]"]').val());
                });
                $('#nominal_dibayar').val(formatRupiah(total));
            }

            hitungTotalTagihan();
            hitungTotalDibayar();

            $(document).on('input', 'input[name*="[dibayar]"]', function() {
                if (!$(this).prop('disabled')) {
                    $(this).val(formatRupiah($(this).val()));
                    hitungTotalDibayar();
                    cekTombolSave($(this).closest('tr'));
                }
            });

            $(document).on('dblclick', 'input[name*="[nominal]"]', function() {
                let $row = $(this).closest('tr');
                let $dibayar = $row.find('input[name*="[dibayar]"]');
                let val = cleanNumber($(this).val());
                $dibayar.val(formatRupiah(val));
                $dibayar.prop('disabled', false).focus();
                hitungTotalDibayar();
            });

            $(document).on('click', '.btnSave', function() {
                let $row = $(this).closest('tr');
                let idBipot = $row.find('input[name*="[dibayar]"]').data('id');
                let dibayar = cleanNumber($row.find('input[name*="[dibayar]"]').val());
                let nominal = cleanNumber($row.find('input[name*="[nominal]"]').val());
                let $btn = $(this);

                if (dibayar <= 0) {
                    alert('Nominal dibayar harus > 0');
                    return;
                }

                $.ajax({
                    url: "{{ route('pembayaran.store') }}",
                    method: 'POST',
                    data: {
                        _token: token,
                        bipot_id: idBipot,
                        dibayar: dibayar,
                        tagihan_id: "{{ $tagihan->id }}"
                    },
                    success: function(res) {
                        if (res.success) {
                            Lobibox.notify('success', {
                                pauseDelayOnHover: true,
                                size: 'mini',
                                rounded: true,
                                icon: 'bx bx-check-circle',
                                delayIndicator: false,
                                continueDelayOnInactiveTab: false,
                                position: 'top right',
                                msg: res.message,
                                sound: false,
                            });
                            $row.find('input[name*="[dibayar]"]').prop('disabled', true);
                            hitungTotalDibayar();
                            cekTombolSave($row);
                            window.location.reload;
                        } else {
                            Lobibox.notify('error', {
                                pauseDelayOnHover: true,
                                size: 'mini',
                                rounded: true,
                                icon: 'bx bx-x-circle',
                                delayIndicator: false,
                                continueDelayOnInactiveTab: false,
                                position: 'top right',
                                msg: res.message,
                                sound: false,
                            });
                        }
                    },
                    error: function() {
                        alert('Gagal menyimpan data!');
                    }
                });
            });

            function cekTombolSave($row) {
                let nominal = cleanNumber($row.find('input[name*="[nominal]"]').val());
                let bayar = cleanNumber($row.find('input[name*="[dibayar]"]').val());
                let $btn = $row.find('.btnSave');

                if (nominal > 0 && bayar >= nominal) {
                    $btn.prop('disabled', true).removeClass('btn-success').addClass('btn-secondary').attr('title',
                        'Lunas');
                } else {
                    $btn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-success').attr('title',
                        '');
                }
            }

            $('#tableDetail tbody tr').each(function() {
                cekTombolSave($(this));
            });

            $(document).on('click', '.btn-delete', function() {
                let id = $(this).data('id');
                let button = $(this);

                if (!confirm('Yakin ingin menghapus data ini?')) {
                    return;
                }

                $.ajax({
                    url: '/pembayaran/' + id,
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

                            window.location.reload;
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

        });
    </script>
@endpush
