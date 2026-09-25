@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h6 class="mb-0">Data Tagihan Lama</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th width="30px">No</th>
                            <th>Nomor Invoice</th>
                            <th>Nomor Pembayaran</th>
                            <th>Nama Mahasiswa</th>
                            <th>Informasi Tagihan</th>
                            <th>Rincian Tagihan</th>
                            <th>Total Tagihan</th>
                            <th>Tanggal Berlaku</th>
                            <th>Tanggal Kadaluarsa</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Status Pembayaran</th>
                            <th class="text-center" width="120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Flagging --}}
    <div class="modal fade" id="modalFlagging" tabindex="-1" aria-labelledby="modalFlaggingLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-flagging">
                    <div class="modal-header">
                        <h6 class="modal-title" id="modalFlaggingLabel">Flagging Pembayaran</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Tandai tagihan ini sebagai sudah dibayar secara manual.</p>
                        <div class="mb-2">
                            <label for="flag_bank" class="form-label">Bank</label>
                            <input type="text" class="form-control" id="flag_bank" name="bank" maxlength="15"
                                placeholder="Contoh: BSI" required>
                        </div>
                        <div class="mb-2">
                            <label for="flag_waktu_pembayaran" class="form-label">Waktu Pembayaran</label>
                            <input type="datetime-local" class="form-control" id="flag_waktu_pembayaran"
                                name="waktu_pembayaran" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Perpanjang --}}
    <div class="modal fade" id="modalPerpanjang" tabindex="-1" aria-labelledby="modalPerpanjangLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-perpanjang">
                    <div class="modal-header">
                        <h6 class="modal-title" id="modalPerpanjangLabel">Perpanjang Tagihan</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Waktu Kadaluarsa Saat Ini</label>
                            <input type="datetime-local" class="form-control" id="perpanjang_lama" disabled>
                        </div>
                        <div class="mb-2">
                            <label for="perpanjang_waktu_kadaluarsa" class="form-label">Waktu Kadaluarsa Baru</label>
                            <input type="datetime-local" class="form-control" id="perpanjang_waktu_kadaluarsa"
                                name="waktu_kadaluarsa" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Ubah --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="form-edit">
                    <div class="modal-header">
                        <h6 class="modal-title" id="modalEditLabel">Ubah Tagihan</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="edit_waktu_berlaku" class="form-label">Waktu Berlaku</label>
                                <input type="datetime-local" class="form-control" id="edit_waktu_berlaku"
                                    name="waktu_berlaku" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="edit_waktu_kadaluarsa" class="form-label">Waktu Kadaluarsa</label>
                                <input type="datetime-local" class="form-control" id="edit_waktu_kadaluarsa"
                                    name="waktu_kadaluarsa" required>
                            </div>
                        </div>
                        <label class="form-label mt-2">Rincian Tagihan</label>
                        <table class="table table-bordered table-sm mb-2">
                            <thead>
                                <tr>
                                    <th>Kode Rincian</th>
                                    <th width="220px">Nominal</th>
                                </tr>
                            </thead>
                            <tbody id="edit_rincian"></tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-end">Total</th>
                                    <th id="edit_total">Rp 0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm">Simpan</button>
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
        let table;
        let currentId = null;
        const baseUrl = "{{ url('tagihan-old') }}";

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function notify(type, msg) {
            Lobibox.notify(type, {
                pauseDelayOnHover: true,
                size: 'mini',
                rounded: true,
                icon: type === 'success' ? 'bx bx-check-circle' : (type === 'warning' ? 'bx bx-error' :
                    'bx bx-x-circle'),
                delayIndicator: false,
                continueDelayOnInactiveTab: false,
                position: 'top right',
                msg: msg,
                sound: false,
            });
        }

        function formatRupiah(angka) {
            return 'Rp ' + (parseInt(angka) || 0).toLocaleString('id-ID');
        }

        function escapeHtml(text) {
            return $('<div>').text(text ?? '').html();
        }

        // Tampilkan pesan error dari response (validasi atau server)
        function handleError(xhr, defaultMsg) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                notify('warning', '⚠ ' + Object.values(xhr.responseJSON.errors)[0][0]);
            } else if (xhr.responseJSON && xhr.responseJSON.message && xhr.status !== 500) {
                notify('error', '✕ ' + xhr.responseJSON.message);
            } else {
                notify('error', defaultMsg);
            }
        }

        // Ambil data tagihan lalu jalankan callback
        function loadTagihan(id, callback) {
            $.get(baseUrl + '/' + id)
                .done(res => callback(res.data))
                .fail(() => notify('error', '✕ Gagal memuat data. Periksa koneksi Anda.'));
        }

        // Kirim form via AJAX, lalu tutup modal & reload tabel
        function submitForm(form, url, method, modal, errorMsg) {
            let button = form.find('button[type="submit"]');
            let label = button.text();
            button.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize() + '&_method=' + method,
                })
                .done(function(res) {
                    notify('success', res.message);
                    modal.modal('hide');
                    table.ajax.reload(null, false);
                })
                .fail(xhr => handleError(xhr, errorMsg))
                .always(() => button.prop('disabled', false).text(label));
        }

        function hitungTotal() {
            let total = 0;
            $('#edit_rincian .input-nominal').each(function() {
                total += parseInt($(this).val()) || 0;
            });
            $('#edit_total').text(formatRupiah(total));
        }

        $(document).ready(function() {
            table = $('#example').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                scrollX: true,
                ajax: {
                    url: "{{ url()->current() }}",
                    error: () => notify('error', '✕ Gagal memuat data. Periksa koneksi Anda.')
                },
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                    emptyTable: 'Belum ada data tagihan.',
                    zeroRecords: 'Tagihan tidak ditemukan. Coba kata kunci lain.',
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nomor_invoice',
                        name: 'nomor_invoice'
                    },
                    {
                        data: 'nomor_pembayaran',
                        name: 'nomor_pembayaran'
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'info',
                        name: 'info'
                    },
                    {
                        data: 'rincian',
                        name: 'rincian'
                    },
                    {
                        data: 'total_nominal',
                        name: 'total_nominal'
                    },
                    {
                        data: 'waktu_berlaku',
                        name: 'waktu_berlaku'
                    },
                    {
                        data: 'waktu_kadaluarsa',
                        name: 'waktu_kadaluarsa'
                    },
                    {
                        data: 'waktu_pembayaran',
                        name: 'waktu_pembayaran'
                    },
                    {
                        data: 'status_pembayaran',
                        name: 'status_pembayaran',
                        searchable: false
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            // Flagging
            $(document).on('click', '.btn-flagging', function() {
                currentId = $(this).data('id');
                $('#form-flagging')[0].reset();
                $('#modalFlagging').modal('show');
            });
            $('#form-flagging').on('submit', function(e) {
                e.preventDefault();
                submitForm($(this), baseUrl + '/' + currentId + '/flagging', 'PUT', $('#modalFlagging'),
                    '✕ Gagal memperbarui data. Coba lagi.');
            });

            // Perpanjang
            $(document).on('click', '.btn-perpanjang', function() {
                currentId = $(this).data('id');
                loadTagihan(currentId, function(data) {
                    $('#form-perpanjang')[0].reset();
                    $('#perpanjang_lama').val(data.waktu_kadaluarsa);
                    $('#modalPerpanjang').modal('show');
                });
            });
            $('#form-perpanjang').on('submit', function(e) {
                e.preventDefault();
                submitForm($(this), baseUrl + '/' + currentId + '/perpanjang', 'PUT', $('#modalPerpanjang'),
                    '✕ Gagal memperbarui data. Coba lagi.');
            });

            // Ubah
            $(document).on('click', '.btn-edit', function() {
                currentId = $(this).data('id');
                loadTagihan(currentId, function(data) {
                    $('#edit_waktu_berlaku').val(data.waktu_berlaku);
                    $('#edit_waktu_kadaluarsa').val(data.waktu_kadaluarsa);

                    let rows = '';
                    data.rincian.forEach(function(item, i) {
                        let kode = (item.kode_rincian || '-').replace(/_/g, ' ').toUpperCase();
                        rows += `<tr>
                            <td class="align-middle">${escapeHtml(kode)}</td>
                            <td>
                                <input type="number" min="0" class="form-control form-control-sm input-nominal"
                                    name="rincian[${i}][nominal]" value="${parseInt(item.nominal) || 0}"
                                    aria-label="Nominal ${escapeHtml(kode)}" required>
                            </td>
                        </tr>`;
                    });
                    $('#edit_rincian').html(rows ||
                        '<tr><td colspan="2" class="text-center text-muted">Tagihan ini tidak memiliki rincian.</td></tr>'
                    );
                    hitungTotal();
                    $('#modalEdit').modal('show');
                });
            });
            $(document).on('input', '#edit_rincian .input-nominal', hitungTotal);
            $('#form-edit').on('submit', function(e) {
                e.preventDefault();
                submitForm($(this), baseUrl + '/' + currentId, 'PUT', $('#modalEdit'),
                    '✕ Gagal memperbarui data. Coba lagi.');
            });

            // Hapus
            $(document).on('click', '.btn-delete', function() {
                if (!confirm('Yakin ingin menghapus tagihan ini?')) {
                    return;
                }

                $.ajax({
                        url: baseUrl + '/' + $(this).data('id'),
                        type: 'DELETE',
                    })
                    .done(function(res) {
                        notify('success', res.message);
                        table.ajax.reload(null, false);
                    })
                    .fail(xhr => handleError(xhr, '✕ Gagal menghapus data. Coba lagi.'));
            });
        });
    </script>
@endpush
