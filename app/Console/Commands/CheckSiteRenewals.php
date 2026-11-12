<?php

namespace App\Console\Commands;

use App\Models\PanelSetting;
use App\Models\Site;
use App\Services\HealthAlerts;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class CheckSiteRenewals extends Command
{
    protected $signature = 'renewals:check';

    protected $description = 'Registrar avisos de los próximos vencimientos de hosting y dominio';

    public function handle(HealthAlerts $alerts): int
    {
        $settings = PanelSetting::find(1);
        if (! $settings?->health_alert_email) {
            return self::SUCCESS;
        }

        $today = CarbonImmutable::today();
        Site::where('status', '!=', 'archived')->where('renewal_alerts_enabled', true)
            ->where(fn ($query) => $query->whereNotNull('hosting_renewal')->orWhereNotNull('domain_renewal'))
            ->lazyById()->each(fn (Site $site) => $alerts->recordRenewals($site, $settings, $today));

        return self::SUCCESS;
    }
}
