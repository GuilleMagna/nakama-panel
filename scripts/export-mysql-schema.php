<?php

// Exporta solo las migraciones del código: nunca consulta ni exporta datos locales.
$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$output = $argv[1] ?? $root.'/database/schema/initial-mysql.sql';
config(['database.connections.schema_export' => [
    'driver' => 'schema_export', 'host' => '127.0.0.1', 'database' => 'schema_export',
    'username' => '', 'password' => '', 'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'engine' => 'InnoDB',
    'version' => '8.0.36',
]]);
Illuminate\Support\Facades\DB::extend('schema_export', function ($config) {
    return new class(fn () => throw new RuntimeException('El exportador no permite conectarse a una base.'), 'schema_export', '', $config) extends Illuminate\Database\MySqlConnection {
        public function isMaria() { return false; }
        public function getServerVersion(): string { return '8.0.36'; }
        public function escape($value, $binary = false)
        {
            if (is_string($value) && ! $binary) {
                return "'".str_replace(["\\", "'", "\0", "\n", "\r", "\x1a"], ["\\\\", "\\'", "\\0", "\\n", "\\r", "\\Z"], $value)."'";
            }
            return parent::escape($value, $binary);
        }
    };
});
$original = Illuminate\Support\Facades\DB::getDefaultConnection();
Illuminate\Support\Facades\DB::setDefaultConnection('schema_export');
try {
    $connection = Illuminate\Support\Facades\DB::connection();
    $queries = $connection->pretend(function () use ($root, $connection) {
        Illuminate\Support\Facades\Schema::create('migrations', function ($table) {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
        });
        foreach (glob($root.'/database/migrations/*.php') as $file) {
            (require $file)->up();
        }
    });
} finally {
    Illuminate\Support\Facades\DB::setDefaultConnection($original);
}
$sql = "-- Nakama Panel: instalación inicial para MySQL/MariaDB.\n-- Importar únicamente en una base VACÍA seleccionada en phpMyAdmin.\n-- Sin datos privados, usuarios, contraseñas ni instrucciones de borrado.\n-- No usar este archivo para actualizar instalaciones existentes.\nSET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;\n\n";
foreach ($queries as $query) {
    $statement = $query['query'];
    foreach ($query['bindings'] as $binding) {
        $value = is_numeric($binding) ? (string) $binding : "'".str_replace("'", "''", (string) $binding)."'";
        $statement = preg_replace_callback('/\?/', fn () => $value, $statement, 1);
    }
    $sql .= $statement.";\n\n";
}
foreach (glob($root.'/database/migrations/*.php') as $file) {
    $name = str_replace("'", "''", basename($file, '.php'));
    $sql .= "INSERT INTO `migrations` (`migration`, `batch`) VALUES ('".$name."', 1);\n";
}
if (! is_dir(dirname($output))) { mkdir(dirname($output), 0755, true); }
if (file_put_contents($output, $sql) === false) { throw new RuntimeException('No se pudo escribir el SQL.'); }
echo 'Esquema exportado: '.count($queries)." sentencias.\n";
