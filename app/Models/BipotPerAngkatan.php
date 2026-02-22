<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BipotPerAngkatan extends Model
{
    protected $table = 'master_bipot_per_angkatan';

    public function bipotSemester()
    {
        return $this->hasMany(BipotPerSemester::class, 'id_bipot_angkatan', 'id');
    }
    public function programKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_program_kuliah');
    }
}
