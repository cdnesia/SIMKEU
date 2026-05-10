@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h6 class="mb-0">Generate Tahun Akademik dan Semester</h6>
            <div class="ms-auto">
                <a href="{{ route('bipot-per-angkatan.index') }}" class="btn btn-sm btn-warning">Kembali</a>
            </div>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('bipot-per-angkatan.create') }}" class="row">
                <div class="mb-3">
                    <label class="form-label">Program Studi</label>
                    <select name="program_studi" id="program_studi" class="form-select select2"
                        data-placeholder="--Pilih Program Studi--">
                        <option value=""></option>
                        @foreach ($prodi as $item)
                            <option value="{{ $item->kode_program_studi }}"
                                {{ old('program_studi') == $item->kode_program_studi ? 'selected' : '' }}>
                                {{ $item->nama_program_studi_idn }}
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
                    <label class="form-label">Kelas Perkuliahan</label>
                    <div class="">
                        @foreach ($kelas as $item)
                            <div class="form-check">
                                <input class="form-check-input @error('jenis_tagihan') is-invalid @enderror" id="i_{{ $item->id }}"
                                    type="checkbox" value="{{ $item->id }}" name="kelas[]">
                                <label class="form-check-label" for="i_{{ $item->id }}">{{ $item->nama_program_perkuliahan }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-success btn-primary btn-sm" name="s" value="simpan">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
