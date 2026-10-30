<?php

namespace Tests\Unit;

use App\Support\DeploymentFiles;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DeploymentFilesTest extends TestCase
{
    public function test_file_comparison_finds_changes_without_reading_secrets_or_dependencies(): void
    {
        $root = sys_get_temp_dir().'/nakama-files-'.bin2hex(random_bytes(8));
        File::makeDirectory($root.'/public', 0700, true);
        File::makeDirectory($root.'/vendor', 0700, true);
        try {
            file_put_contents($root.'/public/.htaccess', 'modified');
            file_put_contents($root.'/unchanged.php', "hello\r\n");
            file_put_contents($root.'/.env', 'never-return-this');
            file_put_contents($root.'/unexpected.zip', 'zip');
            $tracked = ['public/.htaccess' => sha1("blob 8\0original"), 'unchanged.php' => sha1("blob 6\0hello\n")];
            $result = (new DeploymentFiles)->inspect($root, $tracked);
            $this->assertSame(['public/.htaccess'], $result['archivos_versionados_diferentes']);
            $this->assertSame(['unexpected.zip'], $result['otros_archivos_a_revisar']);
            $this->assertStringNotContainsString('never-return-this', json_encode($result));
            $this->assertSame('modified', file_get_contents($root.'/public/.htaccess'));
        } finally {
            File::deleteDirectory($root);
        }
    }
}
