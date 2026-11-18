<?php

namespace App\Services;

use App\Models\PanelSetting;
use App\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class HealthAlerts
{
    public function record(Site $site, string $kind, ?string $previousError, ?string $error): void
    {
        if ($error === null || $previousError !== null || ! $this->recipient($site, $kind, PanelSetting::find(1))) {
            return;
        }

        DB::table('health_alerts')->insert([
            'site_id' => $site->id, 'kind' => $kind, 'detail' => $error,
            'created_at' => now(), 'next_attempt_at' => now(),
        ]);
    }

    public function recordRenewals(Site $site, PanelSetting $settings, CarbonImmutable $today): void
    {
        foreach (Site::RENEWAL_LABELS as $service => $label) {
            $kind = $service.'_renewal';
            $deadline = $site->renewalDeadline($service);
            if (! $deadline || $today->lt($deadline->subWeek()) || $today->gt($deadline)
                || ! $this->recipient($site, $kind, $settings)) {
                continue;
            }

            DB::table('health_alerts')->insertOrIgnore([
                'site_id' => $site->id, 'kind' => $kind, 'renewal_period' => $site->getAttribute($kind),
                'detail' => 'Vencimiento de '.$label.' · '.$site->renewalLabel($service).'. Preparar la factura.',
                'created_at' => now(), 'next_attempt_at' => now(),
            ]);
        }
    }

    public function deliverPending(): void
    {
        $mailer = config('mail.default');
        if (in_array(config('mail.mailers.'.$mailer.'.transport'), ['log', 'array', null], true)) {
            return;
        }

        Cache::lock('health-alert-delivery', 300)->get(function () {
            $settings = PanelSetting::find(1);
            $alerts = DB::table('health_alerts')->whereNull('sent_at')->whereNull('cancelled_at')
                ->where('next_attempt_at', '<=', now())->orderBy('id')->limit(20)->get();

            foreach ($alerts as $alert) {
                $site = Site::find($alert->site_id);
                $recipient = $site ? $this->recipient($site, $alert->kind, $settings) : null;
                $query = DB::table('health_alerts')->where('id', $alert->id);
                if (! $recipient || ! $this->matchesRenewalPeriod($site, $alert->kind, $alert->renewal_period)) {
                    $query->update(['cancelled_at' => now()]);
                    continue;
                }

                $title = match ($alert->kind) {
                    'availability' => 'Falló el chequeo de disponibilidad',
                    'hosting_renewal' => 'Vencimiento de Hosting',
                    'domain_renewal' => 'Vencimiento de Dominio',
                    default => 'Falló la conexión con Nakama Monitor',
                };
                $section = match ($alert->kind) {
                    'availability' => 'availability',
                    'hosting_renewal', 'domain_renewal' => 'renewals',
                    default => 'monitor',
                };
                $url = route('sites.show', $site).'#'.$section;
                $body = "$title\n{$site->name}\n\n{$alert->detail}\nDetectado: {$alert->created_at}\n\n$url";
                try {
                    Mail::raw($body, fn (Message $message) => $message->to($recipient)->subject('Nakama Panel · '.$title));
                    $query->update(['sent_at' => now(), 'delivery_failed' => false]);
                } catch (TransportExceptionInterface) {
                    $query->update(['next_attempt_at' => now()->addMinutes(5), 'delivery_failed' => true]);
                }
            }
        });
    }

    private function matchesRenewalPeriod(Site $site, string $kind, ?string $period): bool
    {
        if ($period === null || $site->getAttribute($kind) === $period) {
            return true;
        }

        $monthly = strlen($period) === 7;
        $deadline = CarbonImmutable::parse($monthly ? $period.'-01' : $period)->startOfDay();
        $nextPeriod = $deadline->addYearNoOverflow()->format($monthly ? 'Y-m' : 'Y-m-d');

        return $deadline->lte(CarbonImmutable::today()) && $site->getAttribute($kind) === $nextPeriod;
    }

    private function recipient(Site $site, string $kind, ?PanelSetting $settings): ?string
    {
        if (in_array($kind, ['hosting_renewal', 'domain_renewal'], true)) {
            return $settings?->getAttribute($kind.'_alerts_enabled') && $site->renewal_alerts_enabled && $site->status !== 'archived'
                ? $settings->health_alert_email : null;
        }

        $enabled = $kind === 'availability' ? $settings?->availability_alerts_enabled : $settings?->monitor_alerts_enabled;

        return $enabled && $site->health_alerts_enabled && $site->status === 'active' ? $settings->health_alert_email : null;
    }
}
