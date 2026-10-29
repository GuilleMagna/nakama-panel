<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = ['client_id', 'name', 'url', 'ownership', 'status', 'admin_url', 'monitor_endpoint', 'monitor_token', 'monitor_interval_hours', 'notes'];

    protected $hidden = ['monitor_token'];

    protected function casts(): array
    {
        return ['monitor_token' => 'encrypted', 'monitor_interval_hours' => 'integer'];
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
