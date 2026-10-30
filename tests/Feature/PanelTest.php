<?php

namespace Tests\Feature;

use App\Livewire\SitesIndex;
use App\Models\Client;
use App\Models\PanelSetting;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_internal_pages_and_mutations_require_login(): void
    {
        foreach (['/', '/clients', '/sites', '/settings', '/clients/create', '/sites/create'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->post('/clients', ['name' => 'No autorizado'])->assertRedirect('/login');
        $this->assertDatabaseCount('clients', 0);
        $this->get('/register')->assertNotFound();
    }

    public function test_login_and_logout(): void
    {
        User::factory()->create(['email' => 'admin@example.test', 'password' => 'AdminPass12345']);
        $this->post('/login', ['email' => 'admin@example.test', 'password' => 'incorrecta'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => 'admin@example.test', 'password' => 'AdminPass12345'])->assertRedirect('/');
        $this->assertAuthenticated();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_failed_logins_are_limited(): void
    {
        User::factory()->create(['email' => 'limited@example.test']);
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', ['email' => 'limited@example.test', 'password' => 'incorrecta']);
        }
        $response->assertSessionHasErrors(['email' => 'Demasiados intentos. Probá nuevamente en un minuto.']);
        $this->assertGuest();
    }

    public function test_admin_can_create_and_edit_clients(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/clients', ['name' => 'Cliente Uno', 'email' => 'contacto@example.test'])->assertRedirect('/clients');
        $client = Client::firstOrFail();
        $this->put('/clients/'.$client->id, ['name' => 'Cliente actualizado'])->assertRedirect('/clients');
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Cliente actualizado']);
        $this->get('/clients?q=actualizado')->assertOk()->assertSee('Cliente actualizado');
    }

    public function test_site_token_is_encrypted_hidden_and_preserved_on_edit(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::create(['name' => 'Cliente']);
        $data = ['name' => 'Sitio', 'url' => 'https://example.test/', 'client_id' => $client->id, 'ownership' => 'client', 'status' => 'active', 'monitor_token' => 'private-monitor-token'];
        $this->post('/sites', $data)->assertSessionHasNoErrors();
        $site = Site::firstOrFail();
        $this->assertSame('https://example.test', $site->url);
        $this->assertSame('private-monitor-token', $site->monitor_token);
        $this->assertNotSame('private-monitor-token', $site->getRawOriginal('monitor_token'));
        $this->assertArrayNotHasKey('monitor_token', $site->toArray());
        $this->get('/sites/'.$site->id.'/edit')->assertOk()->assertDontSee('private-monitor-token');
        $this->put('/sites/'.$site->id, [...$data, 'monitor_token' => '', 'name' => 'Sitio editado'])->assertSessionHasNoErrors();
        $this->assertSame('private-monitor-token', $site->fresh()->monitor_token);
    }

    public function test_invalid_form_does_not_flash_monitor_token(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/sites', ['monitor_token' => 'secret-failed-form'])->assertSessionHasErrors('name');
        $this->assertNull(session()->getOldInput('monitor_token'));
    }

    public function test_site_requires_client_for_client_property_and_rejects_unsafe_urls(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/sites', ['name' => 'Sitio', 'url' => 'javascript:alert(1)', 'ownership' => 'client', 'status' => 'active', 'admin_url' => 'javascript:alert(1)'])->assertSessionHasErrors(['url', 'admin_url', 'client_id']);
        $this->assertDatabaseCount('sites', 0);
    }

    public function test_duplicate_site_url_is_rejected_after_normalizing_trailing_slash(): void
    {
        $this->actingAs(User::factory()->create());
        $data = ['name' => 'Propio', 'url' => 'https://example.test', 'ownership' => 'own', 'status' => 'active'];
        $this->post('/sites', $data)->assertSessionHasNoErrors();
        $this->post('/sites', [...$data, 'url' => 'https://example.test/'])->assertSessionHasErrors('url');
        $this->assertDatabaseCount('sites', 1);
    }

    public function test_interval_inheritance_and_per_site_override(): void
    {
        $this->actingAs(User::factory()->create());
        $inherited = Site::create(['name' => 'Hereda', 'url' => 'https://one.test', 'ownership' => 'own']);
        $own = Site::create(['name' => 'Particular', 'url' => 'https://two.test', 'ownership' => 'own', 'monitor_interval_hours' => 24]);
        $this->assertSame(12, $inherited->effectiveInterval());
        $this->put('/settings', ['monitor_interval_hours' => 6])->assertRedirect('/settings');
        $this->assertSame(6, $inherited->fresh()->effectiveInterval());
        $this->assertSame(24, $own->fresh()->effectiveInterval());
        $this->put('/settings', ['monitor_interval_hours' => 0])->assertSessionHasErrors('monitor_interval_hours');
        $this->assertSame(6, PanelSetting::currentInterval());
    }

    public function test_history_uses_event_date_and_escapes_content(): void
    {
        $this->actingAs(User::factory()->create());
        $site = Site::create(['name' => 'Historia', 'url' => 'https://history.test', 'ownership' => 'own']);
        foreach ([['2025-01-01', 'Rediseño nuevo'], ['2023-01-01', '<script>alert(1)</script>']] as [$date, $title]) {
            $this->post('/sites/'.$site->id.'/events', ['category' => 'redesign', 'title' => $title, 'occurred_on' => $date, 'description' => 'Hecho histórico', 'approximate' => true])->assertSessionHasNoErrors();
        }
        $this->get('/sites/'.$site->id)->assertOk()->assertSeeInOrder(['Rediseño nuevo', '&lt;script&gt;alert(1)&lt;/script&gt;'], false)->assertDontSee('<script>alert(1)</script>', false);
        $this->assertDatabaseCount('site_events', 2);
    }

    public function test_sites_search_status_and_client_filters(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::create(['name' => 'Cliente filtro']);
        Site::create(['name' => 'Alfa Web', 'url' => 'https://alpha.test', 'ownership' => 'client', 'client_id' => $client->id, 'status' => 'active']);
        Site::create(['name' => 'Beta Web', 'url' => 'https://beta.test', 'ownership' => 'own', 'status' => 'paused']);
        Livewire::test(SitesIndex::class)->set('search', 'Alfa')->assertSee('Alfa Web')->assertDontSee('Beta Web');
        Livewire::test(SitesIndex::class)->set('status', 'paused')->assertSee('Beta Web')->assertDontSee('Alfa Web');
        Livewire::test(SitesIndex::class)->set('ownership', 'own')->assertSee('Beta Web')->assertDontSee('Alfa Web');
        Livewire::test(SitesIndex::class)->set('ownership', 'client')->assertSee('Alfa Web')->assertDontSee('Beta Web');
        Livewire::test(SitesIndex::class)->set('client', (string) $client->id)->assertSee('Alfa Web')->assertDontSee('Beta Web');
    }

    public function test_empty_pages_render_without_fake_data(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['/', '/clients', '/sites', '/sites/create', '/clients/create', '/settings'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->assertDatabaseCount('sites', 0);
    }

    public function test_administrator_creation_refuses_second_user(): void
    {
        User::factory()->create();
        $this->artisan('panel:admin')->expectsOutput('Ya existe un administrador. Este comando solo sirve para el alta inicial.')->assertFailed();
        $this->assertDatabaseCount('users', 1);
    }
}
