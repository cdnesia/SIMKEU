@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h6 class="mb-0">{{ $data ? 'Edit' : 'Tambah' }} Perpanjangan Pembayaran</h6>
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
                    <label class="form-label">Nama Mahasiswa</label>
                    <select name="npm" class="form-select select2 @error('npm') is-invalid @enderror"
                        data-placeholder="--Pilih Mahasiswa--">
                        <option value=""></option>
                        @foreach ($mahasiswa as $item)
                            <option value="{{ $item->npm }}"
                                {{ old('npm', $data->npm ?? '') == $item->npm ? 'selected' : '' }}>
                                {{ '[' . $item->npm . '] ' . $item->nama_mahasiswa }}
                            </option>
                        @endforeach
                    </select>

                    @error('npm')
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
                    <label class="form-label">Pilih Tahun Akademik</label>
                    <div class="d-flex align-items-center gap-3">
                        @foreach ($tahun_akademik as $item)
                            <div class="form-check">
                                <input class="form-check-input @error('tahun_akademik') is-invalid @enderror"
                                    name="tahun_akademik[]" type="checkbox" value="{{ $item->kode_tahun_akademik }}"
                                    id="tahun_akademik_{{ $item->kode_tahun_akademik }}"
                                    {{ (is_array(old('tahun_akademik'))
                                            ? in_array($item->kode_tahun_akademik, old('tahun_akademik'))
                                            : isset($data) && in_array($item->kode_tahun_akademik, $data->tahun_akademik ?? []))
                                        ? 'checked'
                                        : '' }}>
                                <label class="form-check-label" for="tahun_akademik_{{ $item->kode_tahun_akademik }}">
                                    {{ $item->kode_tahun_akademik }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('tahun_akademik')
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
