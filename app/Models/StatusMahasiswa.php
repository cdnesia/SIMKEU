<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusMahasiswa extends Model
{
    protected $connection = 'db_siade';
    protected $table = 'master_status_mahasiswa';
}
