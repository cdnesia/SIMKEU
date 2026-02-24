@extends('layouts.app')

@section('content')
    <h6 class="text-uppercase">Edit tagihan mahasiswa</h6>
    <hr>
    <form class="row g-3" method="POST" action="{{ route('tagihan.update', Crypt::encrypt($tagihan->id)) }}">
        @csrf
        @method('PUT')
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Data Mahasiswa</h6>
                </div>
                <div class="card-body">

                    @php
                        use Illuminate\Support\Carbon;
                    @endphp

                    <div class="mb-3">
                        <label class="form-label">Nomor Pokok Mahasiswa</label>
                        <input type="text" class="form-control" value="{{ e($tagihan->npm) }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Mahasiswa</label>
                        <input type="text" class="form-control" value="{{ e($tagihan->nama_mahasiswa) }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Program Studi</label>
                        <input type="text" class="form-control" value="{{ e($tagihan->nama_program_studi) }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fakultas</label>
                        <input type="text" class="form-control" value="{{ e($tagihan->nama_fakultas) }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <input type="text" class="form-control" value="{{ e($tagihan->nama_kelas_perkuliahan) }}"
                            readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tahun Akademik</label>
                        <input type="text" class="form-control" value="{{ e($tagihan->tahun_akademik) }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Tagihan</label>
                        <input type="text" class="form-control" value="{{ e($tagihan->jenis_tagihan) }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Waktu Berakhir</label>
                        <input type="date" name="waktu_berakhir"
                            value="{{ Carbon::parse($tagihan->waktu_berakhir)->format('Y-m-d') }}" class="form-control"
                            required>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Detail Biaya dan Potongan</h6>
                </div>
                <div class="card-body">

                    <table class="table table-bordered" id="tableDetail">
                        <thead>
                            <tr>
                                <th>Biaya / Potongan</th>
                                <th width="200">Nominal</th>
                                <th width="50">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach (array_values((array) $detail) as $index => $row)
                                @php
                                    $safeNominal = isset($row['nominal'])
                                        ? preg_replace('/[^0-9]/', '', (string) $row['nominal'])
                                        : 0;

                                    $safeBipot = (string) ($row['id_bipot'] ?? '');
                                @endphp

                                <tr>
                                    <td>
                                        <select name="detail[{{ $index }}][id_bipot]"
                                            class="form-select bipot select2" required>

                                            <option value="">Pilih</option>

                                            @foreach ($masterBipot as $m)
                                                <option value="{{ e($m->id) }}" data-trx="{{ e($m->trxid) }}"
                                                    {{ $safeBipot === (string) $m->id ? 'selected' : '' }}>
                                                    {{ e($m->nama_bipot) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" name="detail[{{ $index }}][nominal]"
                                            value="{{ $safeNominal }}" class="form-control nominal" inputmode="numeric"
                                            autocomplete="off" required>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm btnHapus">
                                            <i class="bx bx-x-circle me-0"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>

                    <button type="button" id="btnTambah" class="btn btn-primary btn-sm mb-3">
                        + Tambah Biaya / Potongan
                    </button>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Total Tagihan</label>
                            <input type="text" id="total_tagihan" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jumlah Ditagihkan <small class="text-danger"><i>* Doble klik untuk
                                        edit</i></small></label>
                            <input type="text" name="nominal_ditagih" id="nominal_ditagih" class="form-control nominal"
                                value="{{ old('nominal_ditagih') }}" readonly>
                        </div>
                    </div>

                </div>

                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-success btn-sm">
                        Update
                    </button>
                    <a href="{{ route('tagihan.index') }}" class="btn btn-sm btn-warning">Kembali</a>
                </div>

            </div>
        </div>
    </form>
@endsection

@push('js')
    <script>
        $(document).ready(function() {

            function initSelect2(el) {
                el.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    allowClear: true,
                    placeholder: 'Pilih Biaya / Potongan'
                });
            }

            initSelect2($('.select2'));

            let rowIndex = parseInt($('#tableDetail tbody tr').length) || 0;

            function cleanNumber(val) {
                return parseInt(String(val || '').replace(/\D/g, ''), 10) || 0;
            }

            function formatRupiah(val) {
                val = cleanNumber(val);
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function hitungTotal() {
                let total = 0;

                $('#tableDetail tbody tr').each(function() {
                    let trx = parseInt($(this).find('.bipot option:selected').data('trx'));
                    let nominal = cleanNumber($(this).find('.nominal').val());

                    if (trx === -1) total -= nominal;
                    else total += nominal;
                });

                $('#total_tagihan').val(formatRupiah(total));
                let $nd = $('#nominal_ditagih');
                if ($nd.prop('readonly')) {
                    $nd.val(formatRupiah(total));
                }
            }

            $('.nominal').each(function() {
                let n = cleanNumber($(this).val());
                if (n > 0) $(this).val(formatRupiah(n));
            });

            hitungTotal();

            $(document).on('input', '.nominal', function() {
                let n = cleanNumber($(this).val());
                $(this).val(formatRupiah(n));
                hitungTotal();
            });

            $(document).on('change', '.bipot', hitungTotal);

            $('#btnTambah').click(function() {

                let row = `
        <tr>
            <td>
                <select name="detail[${rowIndex}][id_bipot]"
                        class="form-select bipot select2" required>
                    <option value="">Pilih</option>
                    @foreach ($masterBipot as $m)
                        <option value="{{ e($m->id) }}"
                                data-trx="{{ e($m->trxid) }}">
                            {{ e($m->nama_bipot) }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text"
                       name="detail[${rowIndex}][nominal]"
                       class="form-control nominal"
                       inputmode="numeric"
                       autocomplete="off"
                       required>
            </td>
            <td class="text-center">
                <button type="button"
                        class="btn btn-danger btn-sm btnHapus">
                    <i class="bx bx-message-square-x me-0"></i>
                </button>
            </td>
        </tr>`;

                $('#tableDetail tbody').append(row);
                initSelect2($('#tableDetail tbody tr:last .select2'));
                rowIndex++;
            });

            $(document).on('click', '.btnHapus', function() {
                $(this).closest('tr').remove();
                hitungTotal();
            });

            $('#nominal_ditagih').on('dblclick', function() {
                $(this).prop('readonly', false);
            });

            $('form').on('submit', function() {
                $('.nominal').each(function() {
                    $(this).val(cleanNumber($(this).val()));
                });

                let nd = $('#nominal_ditagih');
                nd.val(cleanNumber(nd.val()));
            });

        });
    </script>
@endpush
