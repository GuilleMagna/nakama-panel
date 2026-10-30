#!/bin/sh
# Ejecutado por Deploy HEAD Commit; se detiene ante el primer error.
set -eu
cd /home/bizpills/nakama-panel
PHP=/opt/cpanel/ea-php84/root/usr/bin/php
if [ ! -x "$PHP" ]; then
    echo 'No se encontró PHP 8.4 CLI. Revisar la ruta antes de desplegar.' >&2
    exit 1
fi
if [ ! -f .env ] || [ ! -f vendor/autoload.php ]; then
    echo 'Falta la configuración privada o las dependencias de producción.' >&2
    exit 1
fi
"$PHP" artisan config:clear --no-interaction
"$PHP" artisan migrate --force --no-interaction
"$PHP" artisan view:clear --no-interaction
echo 'Migraciones pendientes aplicadas y vistas actualizadas.'
