@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h6 class="mb-0">{{ $data ? 'Edit' : 'Tambah' }} Jadwal Pembayaran</h6>
            <div class="ms-auto">
                <a href="{{ route($modul . '.index') }}" class="btn btn-sm btn-warning">Kembali</a>
            </div>
        </div>

        <div class="card-body">
            <form method="POST"
                action="{{ $data ? route($modul . '.update', Crypt::encrypt($data->id)) : route($modul . '.store') }}"
                class="row g-3">
                @csrf
                @if ($data)
                    @method('PUT')
                @endif
                <div class="col-md-12">
                    <label class="form-label">Tahun Akademik</label>
                    <select name="tahun_akademik" class="form-select select2 @error('tahun_akademik') is-invalid @enderror" data-placeholder="--Pilih Tahun Akademik--">
                        <option value=""></option>
                        @foreach ($tahun_akademik as $item)
                            <option value="{{ $item->kode_tahun_akademik }}"
                                {{ old('tahun_akademik', $data->tahun_akademik ?? '') == $item->kode_tahun_akademik ? 'selected' : '' }}>
                                {{ $item->kode_tahun_akademik }}
                            </option>
                        @endforeach
                    </select>

                    @error('tahun_akademik')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-6"><label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                        name="tanggal_mulai" value="{{ old('tanggal_mulai', $data->tanggal_mulai ?? '') }}">
                    @error('tanggal_mulai')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-6"><label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai"
                        class="form-control @error('tanggal_selesai') is-invalid @enderror"
                        value="{{ old('tanggal_selesai', $data->tanggal_selesai ?? '') }}">
                    @error('tanggal_selesai')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Pilih Program Studi</label>
                    @foreach ($program_studi as $item)
                        <div class="form-check">
                            <input class="form-check-input @error('kode_program_studi') is-invalid @enderror"
                                name="kode_program_studi[]" type="checkbox" value="{{ $item->kode_program_studi }}"
                                id="kode_program_studi_{{ $item->kode_program_studi }}"
                                {{ (is_array(old('kode_program_studi'))
                                        ? in_array($item->kode_program_studi, old('kode_program_studi'))
                                        : isset($data) && in_array($item->kode_program_studi, $data->kode_program_studi ?? []))
                                    ? 'checked'
                                    : '' }}>
                            <label class="form-check-label" for="kode_program_studi_{{ $item->kode_program_studi }}">
                                {{ $item->nama_program_studi_idn }}
                            </label>
                        </div>
                    @endforeach

                    @error('kode_program_studi')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div>
                    <button type="submit" class="btn btn-success btn-primary btn-sm">
                        {{ $data ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
