<?php

namespace App\Console\Commands;

use App\Models\PanelSetting;
use App\Models\Site;
use App\Services\NakamaMonitor;
use Illuminate\Console\Command;

class CheckSiteMonitors extends Command
{
    protected $signature = 'monitor:check';

    protected $description = 'Consultar los sitios activos cuyo intervalo de monitoreo ya venció';

    public function handle(NakamaMonitor $monitor): int
    {
        $defaultInterval = PanelSetting::currentInterval();
        $now = now();
        $due = Site::query()->where('status', 'active')->whereNotNull('monitor_token')
            ->orderByRaw('monitor_attempted_at IS NOT NULL')->orderBy('monitor_attempted_at')->orderBy('id')
            ->select(['id', 'monitor_attempted_at', 'monitor_interval_hours'])->cursor()
            ->filter(fn (Site $site) => $site->monitor_attempted_at === null
                || $site->monitor_attempted_at->lte($now->copy()->subHours($site->monitor_interval_hours ?? $defaultInterval)))
            ->take(10)->pluck('id')->all();

        foreach (Site::whereIn('id', $due)->where('status', 'active')->get() as $site) {
            $monitor->check($site);
        }
        $this->info('Sitios seleccionados para consulta: '.count($due));

        return self::SUCCESS;
    }
}
