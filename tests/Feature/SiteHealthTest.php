<?php

namespace Tests\Feature;

use App\Models\PanelSetting;
use App\Models\Site;
use App\Models\User;
use App\Services\HealthAlerts;
use App\Services\NakamaMonitor;
use App\Services\SiteAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class SiteHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_checks_record_separate_alerts_and_skip_duplicate_failures(): void
    {
        $site = Site::create(['name' => 'Sitio', 'url' => 'https://health.test', 'ownership' => 'own', 'monitor_token' => 'token']);
        $availabilityUrl = route('sites.availability.check', $site);
        $this->post($availabilityUrl)->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->withoutVite();
        $this->put('/settings', [
            'monitor_interval_hours' => 12, 'availability_interval_minutes' => 30,
            'health_alert_email' => 'admin@example.com', 'availability_alerts_enabled' => 1, 'monitor_alerts_enabled' => 1,
        ])->assertSessionHasNoErrors();

        $availability = \Mockery::mock(SiteAvailability::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $availability->shouldReceive('probe')->with($site->url)->twice()->andThrow(new RuntimeException('HTTP 503.'));
        $this->instance(SiteAvailability::class, $availability);
        $this->post($availabilityUrl)->assertSessionHasErrors('availability');
        $this->post($availabilityUrl)->assertSessionHasErrors('availability');
        $this->assertDatabaseCount('health_alerts', 1);

        $monitor = \Mockery::mock(NakamaMonitor::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $monitor->shouldReceive('request')->once()->andThrow(new RuntimeException('Token rechazado.'));
        $this->instance(NakamaMonitor::class, $monitor);
        $this->post(route('sites.monitor.check', $site))->assertSessionHasErrors('monitor');
        $this->assertDatabaseHas('health_alerts', ['site_id' => $site->id, 'kind' => 'availability', 'detail' => 'HTTP 503.']);
        $this->assertDatabaseHas('health_alerts', ['site_id' => $site->id, 'kind' => 'monitor', 'detail' => 'Token rechazado.']);
        $this->get('/sites')->assertOk()->assertSee('Disponibilidad')->assertSee('No disponible')->assertSee('Falló la conexión');
        $this->get('/settings')->assertOk()->assertSee('Pendiente');
    }

    public function test_delivery_failure_remains_pending_and_retries(): void
    {
        config(['mail.default' => 'smtp']);
        PanelSetting::updateOrCreate(['id' => 1], ['health_alert_email' => 'admin@example.com']);
        $site = Site::create(['name' => 'Sitio', 'url' => 'https://health.test', 'ownership' => 'own'])->fresh();
        $alerts = app(HealthAlerts::class);
        $alerts->record($site, 'availability', null, 'HTTP 503.');
        Mail::shouldReceive('raw')->once()->andThrow(new TransportException('SMTP no disponible'));
        $alerts->deliverPending();
        $this->assertDatabaseHas('health_alerts', ['site_id' => $site->id, 'delivery_failed' => true, 'sent_at' => null]);
        $this->travel(5)->minutes();
        Mail::shouldReceive('raw')->once();
        $alerts->deliverPending();
        $this->assertDatabaseHas('health_alerts', ['site_id' => $site->id, 'delivery_failed' => false, 'sent_at' => now()]);
    }
}
