<?php

namespace App\Http\Controllers;

use App\Services\CronSyncService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(CronSyncService $cronSyncService)
    {
        return view('home');
    }
}
