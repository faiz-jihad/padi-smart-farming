<?php

namespace App\Console\Commands;

use App\Models\Farm;
use App\Services\Admin\AdminWeatherService;
use Illuminate\Console\Command;

class SyncWeatherAndSoilCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'padi:sync-weather-soil {--farm= : ID Lahan tertentu (opsional)} {--force : Paksa refresh tanpa cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan data cuaca BMKG/OpenWeather dan sensor telemetri tanah secara otomatis untuk seluruh lahan pertanian.';

    /**
     * Execute the console command.
     */
    public function handle(AdminWeatherService $weatherAdminService): int
    {
        $farmId = $this->option('farm');

        $this->info('Memulai sinkronisasi otomatis data cuaca & tanah...');

        if ($farmId) {
            $farm = Farm::find($farmId);
            if (! $farm) {
                $this->error("Lahan dengan ID {$farmId} tidak ditemukan.");
                return self::FAILURE;
            }

            $this->line("Menyinkronkan data untuk lahan: {$farm->name}...");
            $success = $weatherAdminService->refreshWeatherData((int) $farmId);

            if ($success) {
                $this->info("✓ Berhasil memperbarui data cuaca & tanah untuk lahan {$farm->name}.");
                return self::SUCCESS;
            }

            $this->error("✗ Gagal memperbarui data untuk lahan {$farm->name}.");
            return self::FAILURE;
        }

        $count = $weatherAdminService->refreshAllFarmsWeatherData();
        $this->info("✓ Sukses menyinkronkan data cuaca & tanah untuk {$count} lahan pertanian.");

        return self::SUCCESS;
    }
}
