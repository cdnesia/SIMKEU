<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BipotPerSemester extends Model
{
    protected $table = 'master_bipot_per_semester';

    protected $casts = [
        'status_mahasiswa' => 'array',
        'status_awal'      => 'array',
    ];

    private static ?\Illuminate\Support\Collection $statusMahasiswaMap = null;
    private static ?\Illuminate\Support\Collection $statusAwalMap = null;

    public function getStatusMahasiswaListAttribute()
    {
        $ids = is_array($this->status_mahasiswa)
            ? $this->status_mahasiswa
            : json_decode($this->status_mahasiswa, true) ?? [];

        self::$statusMahasiswaMap ??= StatusMahasiswa::pluck('nama_status_mahasiswa', 'id');

        return self::$statusMahasiswaMap->only($ids)->values()->toArray();
    }
    public function getJenisMasukMahasiswaListAttribute()
    {
        $ids = is_array($this->status_awal)
            ? $this->status_awal
            : json_decode($this->status_awal, true) ?? [];

        self::$statusAwalMap ??= StatusMasukMahasiswa::pluck('nama_jenis_pendaftaran', 'id');

        return self::$statusAwalMap->only($ids)->values()->toArray();
    }

    public function bipot()
    {
        return $this->belongsTo(Bipot::class, 'id_bipot');
    }
}
