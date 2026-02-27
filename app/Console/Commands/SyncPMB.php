<?php

namespace App\Console\Commands;

use App\Services\CronSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPMB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:pmb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Log::channel('sync_pembayaran')->info('=== START SYNC ===');
        try {
            $total = $this->sync->tagihan();

            $this->info("Tagihan synced: {$total}");
            Log::channel('sync_pembayaran')->info('SYNC BERHASIL', [
                'total_processed' => $total
            ]);

            $this->info('=== SYNC SELESAI ===');
            Log::channel('sync_pembayaran')->info('=== SYNC SELESAI ===');
        } catch (\Throwable $e) {

            $this->error('SYNC GAGAL!');

            Log::channel('sync_pembayaran')->error('SYNC GAGAL', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return 1;
        }
        return 0;
    }
}
