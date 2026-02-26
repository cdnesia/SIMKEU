<?php

namespace App\Console\Commands;

use App\Http\Controllers\SyncController;
use App\Services\CronSyncService;
use Illuminate\Console\Command;

class ProsesPembayaran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pembayaran:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi Tagihan & Pembayaran';

    /**
     * Execute the console command.
     */
    public function __construct(protected CronSyncService $sync)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('=== START SYNC ===');

        $tagihan = $this->sync->tagihan();
        $this->info("Tagihan synced: {$tagihan}");

        $bayar = $this->sync->pembayaran();
        $this->info("Pembayaran synced: {$bayar}");

        $this->info('=== SYNC SELESAI ===');
    }
}
