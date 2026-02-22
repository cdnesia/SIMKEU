<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelasKuliah extends Model
{
    protected $connection = 'db_siade';
    protected $table = 'master_kelas_perkuliahan';
}
