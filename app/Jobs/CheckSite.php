<?php

namespace App\Jobs;

use App\Models\Site;
use App\Services\NakamaMonitor;
use App\Services\SiteAvailability;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckSite implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(public int $siteId, public string $kind) {}

    public function handle(SiteAvailability $availability, NakamaMonitor $monitor): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $site = Site::find($this->siteId);
        if (! $site || $site->status !== 'active') {
            return;
        }

        if ($this->kind === 'availability') {
            $availability->check($site);
        } elseif ($this->kind === 'monitor' && $site->getRawOriginal('monitor_token')) {
            $monitor->check($site);
        }
    }
}
