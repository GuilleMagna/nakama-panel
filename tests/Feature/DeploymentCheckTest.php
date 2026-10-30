<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class DeploymentCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_diagnostic_requires_login(): void
    {
        Process::fake();
        $this->get('/settings/deployment-check')->assertRedirect('/login');
        Process::assertNothingRan();
    }

    public function test_diagnostic_only_reads_git_status_and_returns_file_names(): void
    {
        Process::fake(['*' => Process::result(output: " M public/.htaccess\n?? installer.zip\n")]);
        $this->actingAs(User::factory()->create())->get('/settings/deployment-check')
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('git_disponible', true)
            ->assertJsonPath('archivos_pendientes', [' M public/.htaccess', '?? installer.zip']);
        Process::assertRan(fn ($process) => is_array($process->command)
            && in_array('--no-optional-locks', $process->command)
            && in_array('status', $process->command)
            && ! in_array('reset', $process->command));
    }

    public function test_process_errors_are_not_exposed(): void
    {
        Process::fake(['*' => Process::result(errorOutput: 'private server information', exitCode: 1)]);
        $this->actingAs(User::factory()->create())->get('/settings/deployment-check')
            ->assertOk()->assertJsonPath('git_disponible', false)->assertDontSee('private server information');
    }
}
