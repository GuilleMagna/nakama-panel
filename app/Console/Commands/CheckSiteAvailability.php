<?php

namespace App\Console\Commands;

use App\Models\PanelSetting;
use App\Models\Site;
use App\Services\SiteAvailability;
use Illuminate\Console\Command;

class CheckSiteAvailability extends Command
{
    protected $signature = 'health:check';

    protected $description = 'Verificar la disponibilidad pública de los sitios activos';

    public function handle(SiteAvailability $availability): int
    {
        $interval = PanelSetting::find(1)?->availability_interval_minutes ?? 30;
        $due = now()->subMinutes($interval);
        Site::query()->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('availability_checked_at')->orWhere('availability_checked_at', '<=', $due))
            ->select(['id', 'url'])->chunkById(25, function ($sites) use ($availability) {
                foreach ($sites as $site) {
                    $availability->check($site);
                }
            });

        return self::SUCCESS;
    }
}
