<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_is_encrypted_and_passwords_are_not_rendered_or_serialized(): void
    {
        $this->withoutVite()->actingAs(User::factory()->create());
        $data = ['name' => 'Sitio', 'url' => 'https://access.test', 'ownership' => 'own', 'status' => 'active', 'access' => ['wordpress' => ['username' => 'wp-user', 'password' => 'wp-secret'], 'cpanel' => ['url' => 'https://hosting.test:2083', 'username' => 'cp-user', 'password' => 'cp-secret'], 'ftp' => ['host' => 'ftp.test', 'port' => 22, 'protocol' => 'sftp', 'username' => 'ftp-user', 'password' => 'ftp-secret']]];
        $this->post('/sites', $data)->assertSessionHasNoErrors();
        $site = Site::firstOrFail();
        $this->assertSame('wp-secret', $site->access_credentials['wordpress']['password']);
        $this->assertStringNotContainsString('wp-user', $site->getRawOriginal('access_credentials'));
        $this->assertStringNotContainsString('ftp-secret', $site->getRawOriginal('access_credentials'));
        $this->assertArrayNotHasKey('access_credentials', $site->toArray());
        $this->get('/sites/'.$site->id.'/edit')->assertOk()->assertSee('wp-user')->assertDontSee('wp-secret')->assertDontSee('cp-secret')->assertDontSee('ftp-secret');
        $data['access']['wordpress'] = ['username' => 'updated', 'password' => ''];
        $data['access']['cpanel']['password'] = ' replacement ';
        $this->put('/sites/'.$site->id, $data)->assertSessionHasNoErrors();
        $credentials = $site->fresh()->access_credentials;
        $this->assertSame('wp-secret', $credentials['wordpress']['password']);
        $this->assertSame('updated', $credentials['wordpress']['username']);
        $this->assertSame(' replacement ', $credentials['cpanel']['password']);
    }

    public function test_invalid_access_is_rejected_and_never_flashed(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/sites', ['access' => ['cpanel' => ['url' => 'javascript:alert(1)', 'password' => 'private'], 'ftp' => ['port' => 65536, 'protocol' => 'unsafe']]])->assertSessionHasErrors(['access.cpanel.url', 'access.ftp.port', 'access.ftp.protocol']);
        $this->assertNull(session()->getOldInput('access'));
        $this->assertDatabaseCount('sites', 0);
    }
}
