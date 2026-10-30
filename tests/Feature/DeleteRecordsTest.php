<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletion_requires_login(): void
    {
        $client = Client::create(['name' => 'Cliente']);
        $site = Site::create(['name' => 'Sitio', 'url' => 'https://delete.test', 'ownership' => 'own']);
        $this->delete('/clients/'.$client->id)->assertRedirect('/login');
        $this->delete('/sites/'.$site->id)->assertRedirect('/login');
        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('sites', 1);
    }

    public function test_client_with_sites_is_preserved_and_unrelated_records_are_untouched(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::create(['name' => 'Con sitios']);
        $empty = Client::create(['name' => 'Sin sitios']);
        Site::create(['name' => 'Sitio', 'url' => 'https://client.test', 'ownership' => 'client', 'client_id' => $client->id]);
        $this->delete('/clients/'.$client->id)->assertSessionHasErrors('client');
        $this->delete('/clients/'.$empty->id)->assertRedirect('/clients');
        $this->assertDatabaseHas('clients', ['id' => $client->id]);
        $this->assertDatabaseMissing('clients', ['id' => $empty->id]);
        $this->assertDatabaseCount('sites', 1);
    }

    public function test_site_deletion_removes_its_history_and_keeps_other_sites(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withoutVite();
        $site = Site::create(['name' => 'Eliminar', 'url' => 'https://remove.test', 'ownership' => 'own']);
        $other = Site::create(['name' => 'Conservar', 'url' => 'https://keep.test', 'ownership' => 'own']);
        foreach ([$site, $other] as $record) {
            $record->events()->create(['user_id' => $user->id, 'category' => 'redesign', 'title' => 'Historia', 'occurred_on' => '2025-01-01', 'description' => 'Detalle']);
        }
        $this->get('/sites/'.$site->id)->assertOk()->assertDontSee('data-confirm=', false);
        $this->get('/sites/'.$site->id.'/edit')->assertOk()->assertSee('data-confirm=', false)->assertSee('Eliminar sitio: Eliminar');
        $this->delete('/sites/'.$site->id)->assertRedirect('/sites');
        $this->assertDatabaseMissing('sites', ['id' => $site->id]);
        $this->assertDatabaseMissing('site_events', ['site_id' => $site->id]);
        $this->assertDatabaseHas('sites', ['id' => $other->id]);
        $this->assertDatabaseHas('site_events', ['site_id' => $other->id]);
    }
}
