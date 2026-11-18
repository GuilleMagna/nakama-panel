<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_can_be_added_to_an_existing_site_and_cleared(): void
    {
        $this->withoutVite()->actingAs(User::factory()->create());

        $siteData = [
            'name' => 'Sitio',
            'url' => 'https://contacto.test',
            'ownership' => 'own',
            'status' => 'active',
        ];
        $this->post(route('sites.store'), $siteData)->assertSessionHasNoErrors();
        $site = Site::firstOrFail();
        $this->get(route('sites.show', $site))->assertSee('Todavía no hay un contacto registrado.');

        $contact = [
            'contact_first_name' => 'María',
            'contact_last_name' => 'Pérez',
            'contact_cuit' => '27-12345678-0',
            'contact_email' => 'maria@example.com',
            'contact_phone' => '+54 9 11 5555-0123',
        ];

        $this->put(route('sites.update', $site), $siteData + $contact)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('sites.show', $site));
        $this->assertDatabaseHas('sites', ['id' => $site->id] + $contact);
        $this->get(route('sites.edit', $site))->assertSee('value="27-12345678-0"', false);
        $this->get(route('sites.show', $site))->assertSeeText(array_values($contact));

        $this->put(route('sites.update', $site), $siteData)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sites', ['id' => $site->id] + $contact);

        $this->put(route('sites.update', $site), $siteData + array_fill_keys(array_keys($contact), ''))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sites', ['id' => $site->id] + array_fill_keys(array_keys($contact), null));
    }
}
