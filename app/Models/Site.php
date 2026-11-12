<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonImmutable;

class Site extends Model
{
    protected $fillable = [
        'client_id', 'name', 'url', 'ownership', 'status', 'admin_url', 'monitor_endpoint',
        'monitor_token', 'monitor_interval_hours', 'notes', 'health_alerts_enabled',
        'hosting_renewal', 'domain_renewal', 'renewal_alerts_enabled',
    ];

    public const RENEWAL_LABELS = ['hosting' => 'Hosting', 'domain' => 'Dominio'];

    protected $hidden = ['monitor_token', 'access_credentials'];

    protected function casts(): array
    {
        return [
            'monitor_token' => 'encrypted',
            'access_credentials' => 'encrypted:array',
            'monitor_interval_hours' => 'integer',
            'monitor_snapshot' => 'array',
            'monitor_checked_at' => 'datetime',
            'monitor_attempted_at' => 'datetime',
            'availability_checked_at' => 'datetime',
            'health_alerts_enabled' => 'boolean',
            'renewal_alerts_enabled' => 'boolean',
        ];
    }

    public function renewalDeadline(string $service): ?CarbonImmutable
    {
        $period = $this->getAttribute($service.'_renewal');

        return $period ? CarbonImmutable::parse(strlen($period) === 7 ? $period.'-01' : $period)->startOfDay() : null;
    }

    public function renewalLabel(string $service): ?string
    {
        $period = $this->getAttribute($service.'_renewal');

        return $this->renewalDeadline($service)?->translatedFormat(strlen($period ?? '') === 7 ? 'F Y' : 'd/m/Y');
    }

    public function monitorStatusUrl(): string
    {
        if ($this->monitor_endpoint) {
            return $this->monitor_endpoint;
        }

        $base = strtok($this->url, '?#');

        return rtrim(preg_replace('~^http://~i', 'https://', $base), '/').'/wp-json/nakama-monitor/v1/status';
    }

    public function pendingUpdates(): ?int
    {
        $snapshot = $this->monitor_snapshot;
        if ($snapshot === null) {
            return null;
        }

        return (int) $snapshot['wordpress']['update_available']
            + (int) $snapshot['theme']['update_available']
            + count(array_filter($snapshot['plugins'], fn (array $plugin) => $plugin['update_available']));
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(SiteEvent::class);
    }

    public function effectiveInterval(): int
    {
        return $this->monitor_interval_hours ?? PanelSetting::currentInterval();
    }
}
