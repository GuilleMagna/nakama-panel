<?php

namespace App\Support;

class DeploymentFiles
{
    public function inspect(string $root, array $tracked): array
    {
        $changed = [];
        $directories = ['' => true];
        foreach ($tracked as $path => $hash) {
            $directory = dirname($path);
            while ($directory !== '.') {
                $directories[$directory] = true;
                $directory = dirname($directory);
            }
            $file = $root.'/'.$path;
            if (! is_file($file) || ! is_readable($file) || is_link($file)) {
                $changed[] = $path.' (ausente o no legible)';
                continue;
            }
            $bytes = file_get_contents($file);
            $normalized = str_replace("\r\n", "\n", $bytes);
            if ($this->blobHash($bytes) !== $hash && $this->blobHash($normalized) !== $hash) {
                $changed[] = $path;
            }
        }

        // Candidatos, no un reemplazo completo de git status: solo nombres.
        // No se recorren carpetas desconocidas, dependencias ni datos privados.
        $candidates = [];
        foreach (array_keys($directories) as $directory) {
            $absolute = $root.($directory === '' ? '' : '/'.$directory);
            if (! is_dir($absolute) || is_link($absolute) || ! is_readable($absolute)) {
                continue;
            }
            foreach (scandir($absolute) ?: [] as $name) {
                if ($name === '.' || $name === '..') { continue; }
                $path = ($directory === '' ? '' : $directory.'/').$name;
                if (isset($tracked[$path]) || isset($directories[$path]) || $path === 'deploy/tracked-files.json' || $this->ignored($path)) { continue; }
                $candidates[] = $path.(is_dir($root.'/'.$path) ? '/' : '');
            }
        }

        return ['archivos_versionados_diferentes' => $changed, 'otros_archivos_a_revisar' => array_values(array_unique($candidates)), 'nota_comparacion' => 'Comparación de contenido sin ejecutar Git. Los otros archivos son candidatos; pueden estar ignorados por reglas particulares del servidor. No se modificó ningún archivo.'];
    }

    private function blobHash(string $bytes): string
    {
        return sha1('blob '.strlen($bytes)."\0".$bytes);
    }

    private function ignored(string $path): bool
    {
        foreach (['.git', 'vendor', 'node_modules', 'backups', 'releases', '.idea', '.vscode', 'public/build', 'public/storage', 'storage/app/private', 'storage/app/public', 'storage/framework/testing'] as $directory) {
            if ($path === $directory || str_starts_with($path, $directory.'/')) { return true; }
        }
        if (str_starts_with($path, 'bootstrap/cache/') && str_ends_with($path, '.php')) { return true; }
        if (preg_match('~^storage/(logs|framework/(cache|sessions|views))/~', $path)) { return true; }
        $name = basename($path);
        foreach (['.env', '.env.*', 'auth.json', '*.pem', '*.key', '*.sql', '*.sqlite', '*.sqlite3', '*.log', '.phpunit.result.cache', '.phpunit.cache', '.DS_Store', 'Thumbs.db', 'nakama-panel-runtime.zip', 'runtime-part-*.zip', '.phpactor.json'] as $pattern) {
            if (fnmatch($pattern, $name)) { return true; }
        }
        return $path === 'public/hot';
    }
}
