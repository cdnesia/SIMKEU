@extends('layouts.app')

@section('content')
    <h6 class="text-uppercase">Tmbah tagihan manual</h6>
    <hr>
    <form class="row g-3" method="POST" action="{{ route('tagihan.store') }}?t=manual">
        @csrf

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Data Mahasiswa</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nomor Pokok Mahasiswa</label>
                        <input type="text" class="form-control" name="npm" value="{{ old('npm') }}"
                            placeholder="Nomor Pokok Mahasiswa" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Mahasiswa</label>
                        <input type="text" class="form-control" name="nama_mahasiswa" value="{{ old('nama_mahasiswa') }}"
                            placeholder="Nama Mahasiswa" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Program Studi</label>
                        <select name="program_studi" id="program_studi" class="form-select select2"
                            data-placeholder="--Pilih Program Studi--">
                            <option value=""></option>
                            @foreach ($program_studi as $item)
                                <option value="{{ $item->kode_program_studi }}"
                                    {{ old('program_studi') == $item->kode_program_studi ? 'selected' : '' }}>
                                    {{ $item->nama_program_studi_idn }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas" id="kelas" class="form-select select2"
                            data-placeholder="--Pilih Kelas Perkuliahan--">
                            <option value=""></option>
                            @foreach ($kelas as $item)
                                <option value="{{ $item->id }}" {{ old('kelas') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_program_perkuliahan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tahun Akademik</label>
                        <select name="tahun_akademik" id="tahun_akademik" class="form-select select2"
                            data-placeholder="--Pilih Tahun Akademik--">
                            <option value=""></option>
                            @foreach ($tahun_akademik as $item)
                                <option value="{{ $item->kode_tahun_akademik }}"
                                    {{ old('tahun_akademik') == $item->kode_tahun_akademik ? 'selected' : '' }}>
                                    {{ $item->nama_tahun_akademik }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Tagihan</label>
                        <input type="text" class="form-control" name="jenis_tagihan" value="{{ old('jenis_tagihan') }}"
                            required placeholder="Jenis Tagihan">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Waktu Berakhir</label>
                        <input type="date" name="waktu_berakhir" class="form-control"
                            value="{{ old('waktu_berakhir') }}" required>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Detail Biaya / Potongan</h6>
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
                            @if (old('detail'))
                                @foreach (old('detail') as $index => $row)
                                    <tr>
                                        <td>
                                            <select name="detail[{{ $index }}][id_bipot]"
                                                class="form-select bipot select2" required>
                                                <option value="">Pilih</option>
                                                @foreach ($masterBipot as $m)
                                                    <option value="{{ e($m->id) }}" data-trx="{{ e($m->trxid) }}"
                                                        {{ (string) $row['id_bipot'] === (string) $m->id ? 'selected' : '' }}>
                                                        {{ e($m->nama_bipot) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="detail[{{ $index }}][nominal]"
                                                value="{{ preg_replace('/[^0-9]/', '', $row['nominal'] ?? 0) }}"
                                                class="form-control nominal" inputmode="numeric" autocomplete="off"
                                                required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm btnHapus"><i
                                                    class="bx bx-x-circle me-0"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>

                    <button type="button" id="btnTambah" class="btn btn-primary btn-sm mb-3">+ Tambah Biaya /
                        Potongan</button>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Total Tagihan</label>
                            <input type="text" id="total_tagihan" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jumlah Ditagihkan <small class="text-danger"><i>* Doble klik untuk
                                        edit</i></small></label>
                            <input type="text" name="nominal_ditagih" id="nominal_ditagih"
                                value="{{ old('nominal_ditagih') }}" class="form-control nominal" readonly>
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
                el.each(function() {
                    let $this = $(this);
                    $this.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        allowClear: true,
                        placeholder: $this.data('placeholder') || '',
                    });
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

            function updateNominalDitagih() {
                let total = 0;
                $('#tableDetail tbody tr').each(function() {
                    let trx = parseInt($(this).find('.bipot option:selected').data('trx')) || 1;
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
                $(this).val(formatRupiah($(this).val()));
            });

            updateNominalDitagih();

            $(document).on('input', '.nominal', function() {
                $(this).val(formatRupiah($(this).val()));
                updateNominalDitagih();
            });
            $(document).on('change', '.bipot', updateNominalDitagih);

            $('#btnTambah').click(function() {
                let row = `<tr>
            <td>
                <select name="detail[${rowIndex}][id_bipot]" class="form-select bipot select2" required data-placeholder="--Pilih Biaya/Potongan--">
                    <option value=""></option>
                    @foreach ($masterBipot as $m)
                        <option value="{{ e($m->id) }}" data-trx="{{ e($m->trxid) }}">{{ e($m->nama_bipot) }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="detail[${rowIndex}][nominal]" class="form-control nominal" inputmode="numeric" autocomplete="off" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btnHapus"><i class="bx bx-x-circle me-0"></i></button>
            </td>
        </tr>`;
                $('#tableDetail tbody').append(row);
                initSelect2($('#tableDetail tbody tr:last .select2'));
                rowIndex++;
            });

            $(document).on('click', '.btnHapus', function() {
                $(this).closest('tr').remove();
                updateNominalDitagih();
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
