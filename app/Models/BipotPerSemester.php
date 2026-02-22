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

    public function getStatusMahasiswaListAttribute()
    {
        $ids = is_array($this->status_mahasiswa)
            ? $this->status_mahasiswa
            : json_decode($this->status_mahasiswa, true) ?? [];
        return StatusMahasiswa::whereIn('id', $ids)
            ->pluck('nama_status_mahasiswa')
            ->toArray();
    }
    public function getJenisMasukMahasiswaListAttribute()
    {
        $ids = is_array($this->status_awal)
            ? $this->status_awal
            : json_decode($this->status_awal, true) ?? [];
        return StatusMasukMahasiswa::whereIn('id', $ids)
            ->pluck('nama_jenis_pendaftaran')
            ->toArray();
    }

    public function bipot()
    {
        return $this->belongsTo(Bipot::class, 'id_bipot');
    }
}
