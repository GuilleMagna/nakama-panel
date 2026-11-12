<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanelSetting extends Model
{
    protected $fillable = [
        'monitor_interval_hours', 'availability_interval_minutes', 'health_alert_email',
        'availability_alerts_enabled', 'monitor_alerts_enabled',
    ];

    public static function currentInterval(): int
    {
        return (int) (self::find(1)?->monitor_interval_hours ?? 12);
    }
}
