<?php

namespace Tests\Feature;

use App\Models\PanelSetting;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteRenewalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_month_and_exact_date_have_distinct_notice_days_without_duplicates(): void
    {
        $this->actingAs(User::factory()->create())->withoutVite();
        $siteData = ['name' => 'Sitio', 'url' => 'https://renew.test', 'ownership' => 'own', 'status' => 'active'];
        $this->post('/sites', [...$siteData, 'hosting_renewal' => '2027-02-30'])->assertSessionHasErrors('hosting_renewal');
        $this->post('/sites', [...$siteData, 'hosting_renewal' => '2027-01', 'domain_renewal' => '2027-01-02'])->assertSessionHasNoErrors();
        $site = Site::firstOrFail();
        PanelSetting::updateOrCreate(['id' => 1], ['health_alert_email' => 'admin@example.com']);

        $this->travelTo(now()->setDate(2026, 12, 24));
        $this->artisan('renewals:check')->assertSuccessful();
        $this->assertDatabaseCount('health_alerts', 0);

        $this->travelTo(now()->addDay());
        $this->artisan('renewals:check')->assertSuccessful();
        $this->artisan('renewals:check')->assertSuccessful();
        $this->assertDatabaseCount('health_alerts', 1);
        $this->assertDatabaseHas('health_alerts', ['site_id' => $site->id, 'kind' => 'hosting_renewal', 'renewal_period' => '2027-01']);

        $this->travelTo(now()->addDay());
        $this->artisan('renewals:check')->assertSuccessful();
        $this->assertDatabaseCount('health_alerts', 2);
        $this->assertDatabaseHas('health_alerts', ['site_id' => $site->id, 'kind' => 'domain_renewal', 'renewal_period' => '2027-01-02']);
        $this->get(route('sites.show', $site))->assertOk()->assertSee('25/12/2026')->assertSee('26/12/2026');
    }

    public function test_due_renewals_advance_without_email_and_can_be_retried(): void
    {
        $this->travelTo(now()->setDate(2028, 2, 29));
        $site = Site::create([
            'name' => 'Sitio', 'url' => 'https://renew.test', 'ownership' => 'own',
            'hosting_renewal' => '2026-02', 'domain_renewal' => '2028-02-29',
            'renewal_alerts_enabled' => false,
        ]);

        $this->artisan('renewals:check')->assertSuccessful();
        $this->artisan('renewals:check')->assertSuccessful();

        $site->refresh();
        $this->assertSame('2029-02', $site->hosting_renewal);
        $this->assertSame('2029-02-28', $site->domain_renewal);
        $this->assertDatabaseCount('health_alerts', 0);
    }

    public function test_global_and_site_preferences_control_renewal_alerts_independently_from_health(): void
    {
        $this->travelTo(now()->setDate(2026, 12, 25));
        PanelSetting::updateOrCreate(['id' => 1], [
            'health_alert_email' => 'admin@example.com', 'hosting_renewal_alerts_enabled' => false,
        ]);
        $site = Site::create([
            'name' => 'Sitio', 'url' => 'https://renew.test', 'ownership' => 'own',
            'hosting_renewal' => '2027-01', 'domain_renewal' => '2027-01',
            'health_alerts_enabled' => false, 'renewal_alerts_enabled' => false,
        ]);
        $this->artisan('renewals:check')->assertSuccessful();
        $this->assertDatabaseCount('health_alerts', 0);
        $site->update(['renewal_alerts_enabled' => true]);
        $this->artisan('renewals:check')->assertSuccessful();
        $this->assertDatabaseCount('health_alerts', 1);
        $this->assertDatabaseHas('health_alerts', ['site_id' => $site->id, 'kind' => 'domain_renewal']);
    }
}
