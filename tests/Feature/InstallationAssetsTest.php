<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class InstallationAssetsTest extends TestCase
{
    public function test_mysql_export_is_reproducible_and_does_not_export_users(): void
    {
        $path = sys_get_temp_dir().'/nakama-schema-'.bin2hex(random_bytes(8)).'.sql';
        try {
            $process = new Process([PHP_BINARY, base_path('scripts/export-mysql-schema.php'), $path], base_path());
            $process->mustRun();
            $sql = file_get_contents($path);
            $this->assertSame(file_get_contents(base_path('database/schema/initial-mysql.sql')), $sql);
            $this->assertSame(13, preg_match_all('/^create table `/m', $sql));
            $this->assertSame(4, substr_count($sql, 'INSERT INTO `migrations`'));
            $this->assertDoesNotMatchRegularExpression('/^\s*(DROP|DELETE|TRUNCATE|UPDATE)\s/im', $sql);
            $this->assertDoesNotMatchRegularExpression('/INSERT INTO `users`/i', $sql);
        } finally {
            if (is_file($path)) { unlink($path); }
        }
    }

    public function test_admin_sql_contains_only_a_compatible_hash_and_no_default_account(): void
    {
        $root = sys_get_temp_dir().'/nakama-admin-test-'.bin2hex(random_bytes(8));
        File::makeDirectory($root.'/scripts', 0700, true);
        File::copy(base_path('scripts/admin-sql.php'), $root.'/scripts/admin-sql.php');
        try {
            $process = new Process([PHP_BINARY, $root.'/scripts/admin-sql.php']);
            $process->setInput(json_encode(['name' => "Nombre O'Prueba", 'email' => 'test@example.test', 'password' => 'SoloPrueba123456']));
            $process->mustRun();
            $sql = file_get_contents($root.'/storage/app/private/admin-bootstrap.sql');
            $this->assertStringNotContainsString('SoloPrueba123456', $sql);
            $this->assertStringNotContainsString('SoloPrueba123456', $process->getOutput());
            $this->assertStringContainsString('WHERE NOT EXISTS (SELECT 1 FROM `users`)', $sql);
            preg_match('/\$2y\$12\$[A-Za-z0-9.\/]{53}/', $sql, $matches);
            $this->assertNotEmpty($matches);
            $this->assertTrue(password_verify('SoloPrueba123456', $matches[0]));
        } finally {
            File::deleteDirectory($root);
        }
    }

    public function test_invalid_admin_details_do_not_create_a_file(): void
    {
        $root = sys_get_temp_dir().'/nakama-admin-test-'.bin2hex(random_bytes(8));
        File::makeDirectory($root.'/scripts', 0700, true);
        File::copy(base_path('scripts/admin-sql.php'), $root.'/scripts/admin-sql.php');
        try {
            $process = new Process([PHP_BINARY, $root.'/scripts/admin-sql.php']);
            $process->setInput(json_encode(['name' => 'Prueba', 'email' => 'incorrecto', 'password' => 'corta']));
            $process->run();
            $this->assertSame(1, $process->getExitCode());
            $this->assertFileDoesNotExist($root.'/storage/app/private/admin-bootstrap.sql');
        } finally {
            File::deleteDirectory($root);
        }
    }
}
