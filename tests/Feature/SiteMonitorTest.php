<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use App\Services\NakamaMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SiteMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitor_uses_saved_token_and_keeps_previous_result_after_failure(): void
    {
        $site = Site::create([
            'name' => 'WordPress de prueba', 'url' => 'http://example.test/blog',
            'ownership' => 'own', 'monitor_token' => 'token-de-prueba',
        ]);
        $url = route('sites.monitor.check', $site);
        $this->post($url)->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->withoutVite();

        $client = \Mockery::mock(NakamaMonitor::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $client->shouldReceive('request')->with('https://example.test/blog/wp-json/nakama-monitor/v1/status', 'token-de-prueba', $site->url)
            ->once()->andReturn([
                'monitor_version' => '0.3.6',
                'wordpress' => ['version' => '6.8', 'update_available' => true, 'new_version' => '6.8.1'],
                'theme' => ['name' => 'Tema', 'version' => '1.0', 'update_available' => false],
                'plugins' => ['items' => [['name' => 'Plugin de prueba', 'version' => '2.0', 'update_available' => true, 'new_version' => '2.1']]],
            ]);
        $client->shouldReceive('request')->once()->andThrow(new RuntimeException('Conexión rechazada.'));
        $this->instance(NakamaMonitor::class, $client);

        $this->post($url)->assertRedirect(route('sites.show', $site).'#monitor')->assertSessionHasNoErrors();
        $site->refresh();
        $this->assertSame(2, $site->pendingUpdates());
        $this->assertNotNull($site->monitor_checked_at);
        $this->get(route('sites.show', $site))->assertOk()->assertSee('Plugin de prueba')->assertSee('2.1')->assertDontSee('token-de-prueba');

        $this->post($url)->assertSessionHasErrors(['monitor' => 'Conexión rechazada.']);
        $this->assertSame(2, $site->fresh()->pendingUpdates());
        $this->assertSame('Conexión rechazada.', $site->fresh()->monitor_error);
    }

    public function test_different_host_and_private_ip_are_rejected_before_request(): void
    {
        $site = Site::create([
            'name' => 'Sitio', 'url' => 'https://127.0.0.1', 'ownership' => 'own',
            'monitor_token' => 'token-de-prueba', 'monitor_endpoint' => 'https://example.test/status',
        ]);
        $monitor = app(NakamaMonitor::class);
        $this->assertStringContainsString('mismo dominio', $monitor->check($site));
        $site->update(['monitor_endpoint' => null]);
        $this->assertStringContainsString('dirección pública', $monitor->check($site));
        $this->assertNull($site->fresh()->monitor_snapshot);
    }

    public function test_automatic_check_only_runs_for_active_due_sites_with_token(): void
    {
        $due = Site::create(['name' => 'Pendiente', 'url' => 'https://due.test', 'ownership' => 'own', 'monitor_token' => 'token']);
        $recent = Site::create(['name' => 'Reciente', 'url' => 'https://recent.test', 'ownership' => 'own', 'monitor_token' => 'token', 'monitor_interval_hours' => 24]);
        $recent->forceFill(['monitor_attempted_at' => now()->subHours(13)])->save();
        Site::create(['name' => 'Pausado', 'url' => 'https://paused.test', 'ownership' => 'own', 'monitor_token' => 'token', 'status' => 'paused']);
        Site::create(['name' => 'Sin token', 'url' => 'https://empty.test', 'ownership' => 'own']);

        $this->mock(NakamaMonitor::class)->shouldReceive('check')->once()
            ->withArgs(fn (Site $site) => $site->is($due))->andReturnNull();
        $this->artisan('monitor:check')->assertSuccessful();
    }
}
