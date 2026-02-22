<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusMasukMahasiswa extends Model
{
    protected $connection = 'db_siade';
    protected $table = 'master_jenis_pendaftaran';
}
