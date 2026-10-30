<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientWhatsappTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_link_uses_digits_and_opens_web_in_new_tab(): void
    {
        $this->withoutVite()->actingAs(User::factory()->create());
        $client = Client::create(['name' => 'Contacto', 'phone' => '+54 (9) 11-1234-5678']);
        $this->assertSame('https://web.whatsapp.com/send?phone=5491112345678', $client->whatsappWebUrl());
        $this->get('/clients')->assertOk()->assertSee('https://web.whatsapp.com/send?phone=5491112345678', false)
            ->assertSee('target="_blank"', false)->assertSee('Abrir WhatsApp de Contacto');
        $this->assertNull((new Client(['phone' => 'sin teléfono']))->whatsappWebUrl());
        $this->assertNull((new Client)->whatsappWebUrl());
        $this->assertSame('https://web.whatsapp.com/send?phone=5491112345678', (new Client(['phone' => '0054 9 11 1234 5678']))->whatsappWebUrl());
    }
}
