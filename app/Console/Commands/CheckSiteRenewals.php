<?php

namespace App\Console\Commands;

use App\Models\PanelSetting;
use App\Models\Site;
use App\Services\HealthAlerts;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckSiteRenewals extends Command
{
    protected $signature = 'renewals:check';

    protected $description = 'Actualizar vencimientos anuales y registrar avisos de hosting y dominio';

    public function handle(HealthAlerts $alerts): int
    {
        $settings = PanelSetting::find(1);
        $today = CarbonImmutable::today();

        Site::where('status', '!=', 'archived')
            ->where(fn ($query) => $query->whereNotNull('hosting_renewal')->orWhereNotNull('domain_renewal'))
            ->select('id')->lazyById()->each(function (Site $candidate) use ($alerts, $settings, $today) {
                DB::transaction(function () use ($candidate, $alerts, $settings, $today) {
                    $site = Site::where('status', '!=', 'archived')->lockForUpdate()->find($candidate->id);
                    if (! $site) {
                        return;
                    }

                    if ($settings?->health_alert_email) {
                        $alerts->recordRenewals($site, $settings, $today);
                    }

                    foreach (Site::RENEWAL_LABELS as $service => $label) {
                        $deadline = $site->renewalDeadline($service);
                        if (! $deadline || $deadline->gt($today)) {
                            continue;
                        }

                        $years = $today->year - $deadline->year;
                        $next = $deadline->addYearsNoOverflow($years);
                        if ($next->lte($today)) {
                            $next = $deadline->addYearsNoOverflow($years + 1);
                        }

                        $field = $service.'_renewal';
                        $format = strlen($site->getAttribute($field)) === 7 ? 'Y-m' : 'Y-m-d';
                        $site->setAttribute($field, $next->format($format));
                    }

                    if ($site->isDirty()) {
                        $site->save();
                        if ($settings?->health_alert_email) {
                            $alerts->recordRenewals($site, $settings, $today);
                        }
                    }
                });
            });

        return self::SUCCESS;
    }
}
