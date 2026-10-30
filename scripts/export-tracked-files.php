<?php

// Ejecutar localmente después de git add. Solo exporta nombres y hashes del índice.
require dirname(__DIR__).'/vendor/autoload.php';
$root = dirname(__DIR__);
$process = new Symfony\Component\Process\Process(['git', '-c', 'safe.directory='.str_replace('\\', '/', $root), 'ls-files', '--stage', '-z'], $root);
$process->mustRun();
$files = [];
foreach (explode("\0", $process->getOutput()) as $entry) {
    if (preg_match('/^100[0-9]{3} ([a-f0-9]{40}) 0\t(.+)$/s', $entry, $matches) && $matches[2] !== 'deploy/tracked-files.json') {
        $files[$matches[2]] = $matches[1];
    }
}
file_put_contents($root.'/deploy/tracked-files.json', json_encode($files, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
echo 'Manifiesto de archivos versionados generado sin datos del servidor.'.PHP_EOL;
