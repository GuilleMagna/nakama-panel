<?php

// Recibe la contraseña por stdin; no usar argumentos ni guardar texto plano.
$input = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
if ($name === '' || mb_strlen($name) > 255 || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255 || strlen($password) < 12 || ! preg_match('/[a-zA-Z]/', $password) || ! preg_match('/[0-9]/', $password)) {
    fwrite(STDERR, "Revisá nombre, correo y contraseña (12 caracteres, letras y números).\n");
    exit(1);
}
$quote = static fn ($value) => "'".str_replace(["\\", "'", "\0", "\n", "\r", "\x1a"], ["\\\\", "\\'", "\\0", "\\n", "\\r", "\\Z"], $value)."'";
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
unset($input['password'], $password);
$sql = "-- Alta inicial: importar después de initial-mysql.sql.\n-- Contiene un hash de contraseña, no su texto plano. Mantener privado.\nSET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
$sql .= 'INSERT INTO `users` (`name`, `email`, `password`, `created_at`, `updated_at`) SELECT '.$quote($name).', '.$quote($email).', '.$quote($hash).", NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM `users`);\n";
$path = dirname(__DIR__).'/storage/app/private/admin-bootstrap.sql';
if (! is_dir(dirname($path))) { mkdir(dirname($path), 0700, true); }
if (file_put_contents($path, $sql, LOCK_EX) === false) { throw new RuntimeException('No se pudo guardar el SQL privado.'); }
@chmod($path, 0600);
echo "Archivo privado generado: ".$path."\nNo contiene la contraseña en texto plano. No subir a Git.\n";
