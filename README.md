# Nakama Panel

Panel interno de Whileam para gestionar sitios WordPress, clientes y su historia. Destino previsto: `panel.whileam.com.ar`.

## Estado de la primera entrega

Aplicación ejecutable con acceso privado, alta/edición de clientes y sitios, listado interactivo con filtros y paginación, ficha del sitio, línea de tiempo y frecuencia global/particular. La conexión del monitor puede guardarse con token cifrado, pero todavía no se realizan consultas.

Todavía pendientes: monitoreo automático, contraseñas de backend/cPanel/FTP, proveedores, ingresos/egresos, servicios/cuotas y avisos. No hay facturación ni procesamiento de pagos. No está desplegado en producción.

## Tecnología

Laravel 12, Livewire 4, Blade, Tailwind CSS 4 y Vite. PHP >= 8.2. Desarrollo y pruebas con SQLite; producción prevista con MySQL/MariaDB. Las versiones resueltas están en `composer.lock` y `pnpm-lock.yaml`.

## Preparación local desde un clon

Requiere PHP con PDO SQLite, Composer, Node compatible con Vite y pnpm. En PowerShell, desde `C:\localhost\nakama-panel`:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File database/database.sqlite
php artisan migrate
pnpm install --frozen-lockfile
pnpm build
php artisan panel:admin
php artisan serve --host=127.0.0.1 --port=8017
```

Copiar `.env` y crear SQLite solo durante la primera preparación si todavía no existen. En la copia local ya preparada están las dependencias, recursos compilados y base vacía: solo falta crear el administrador con `php artisan panel:admin` y arrancar el servidor.

Abrir http://127.0.0.1:8017. El comando de alta solicita nombre, correo y contraseña sin mostrarla; no hay usuario ni contraseña predeterminados. Solo crea el administrador si aún no existe uno.

## Verificaciones

```powershell
php artisan test
pnpm build
```

Las pruebas usan una base SQLite en memoria independiente de la base local. No introducen datos de demostración en el panel.

## Producción

Configurar MySQL/MariaDB, HTTPS, `APP_ENV=production`, `APP_DEBUG=false`, URL y cookies seguras en el entorno del servidor. Apuntar el subdominio a `public/`; no publicar la raíz del proyecto. Conservar la clave de cifrado y respaldarla separadamente: cambiarla sin migración vuelve ilegibles los tokens guardados. No ejecutar `key:generate` sobre una instalación que ya almacene secretos.

La configuración de frecuencia es funcional pero el motor de consultas y el control diario económico aún no están implementados. No configurar un cron suponiendo que esta entrega ya los ejecuta.

## Documentación

- [Requerimientos](docs/REQUERIMIENTOS.md)
- [Arquitectura](docs/ARQUITECTURA.md)
- [Plan de entregas](docs/PLAN.md)
- [Trabajo con Git](docs/DESARROLLO.md)
- [Alcance de la entrega 01](docs/ENTREGA-01.md)

Repositorio público: https://github.com/GuilleMagna/nakama-panel. Trabajar con ramas y pull requests. No versionar `.env`, tokens, contraseñas, datos reales ni respaldos. Los documentos de este repositorio son la copia vigente del relevamiento.

Instalación sin Terminal del hosting: ver docs/INSTALACION-CPANEL.md.
