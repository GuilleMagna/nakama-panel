# Instalación inicial con cPanel y phpMyAdmin

Este procedimiento instala la entrega 01 sin Terminal del hosting. No crea un instalador web ni publica scripts de administración. No sirve para actualizar una base con datos.

## Base de datos

Base confirmada: `bizpills_panel`. Usuario: `bizpills_userpanel`, asociado a la base con permisos. Contraseña: completar directamente en el servidor.

En phpMyAdmin seleccionar esa base VACÍA e importar `database/schema/initial-mysql.sql`. El archivo se genera desde las migraciones de Laravel, sin consultar ni exportar datos locales. Incluye la tabla `migrations` para que próximas actualizaciones reconozcan las migraciones ya aplicadas. No incluye usuarios ni contraseñas.

## Archivos

El repositorio está en `/home/bizpills/nakama-panel`, rama `feat/base-clientes-sitios`. El paquete complementario `nakama-panel-runtime.zip` solo incluye `vendor/` con dependencias de producción y `public/build/` con recursos compilados. Extraerlo en la raíz del repositorio. No contiene `.env`, base de datos, `.git`, logs ni datos locales.

En un clon nuevo `bootstrap/cache` y las carpetas de `storage` deben existir y ser escribibles por el usuario del hosting. No usar permisos 777. No subir cachés generadas en otro equipo.

## Configuración privada

Crear `.env` en la raíz del proyecto a partir de `deploy/production.env.example`. Completar `APP_KEY` con una clave nueva de producción y `DB_PASSWORD` directamente en cPanel. No reutilizar la clave local. Conservar la clave estable y respaldada después de empezar a guardar tokens.

La contraseña MySQL debe encerrarse entre comillas simples si contiene espacios, `#` o `$`. No compartir `.env` por chat ni versionarlo. Guardar el archivo con permisos restrictivos permitidos por el hosting, preferentemente 600.

Para generar una clave sin mostrar credenciales existentes, en PowerShell local:

```powershell
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Copiar el resultado a `APP_KEY` directamente en el servidor. No ejecutar este comando repetidamente para reemplazar una clave que ya protege datos.

## Administrador

Las tablas se instalan vacías. El alta del administrador se resuelve aparte y requiere una contraseña elegida directamente por el usuario. No usar cuentas ni contraseñas predeterminadas ni funciones SQL de hash que Laravel no pueda verificar. No insertar el texto de la contraseña en `users.password`.

## Subdominio y PHP

Apuntar `panel.whileam.com.ar` a `/home/bizpills/nakama-panel/public`, nunca a la raíz del proyecto. Configurar PHP >=8.2 con PDO MySQL y las extensiones de Laravel; comprobar que el hosting ofrece las extensiones exigidas por `composer.lock`. Activar HTTPS antes de usar el login, ya que las cookies de sesión son seguras.

## Verificación

Comprobar login, alta/edición de cliente y sitio, historia y configuración. Verificar que `.env`, `.git` y `vendor` no sean accesibles desde el subdominio. El monitoreo automático y la gestión económica siguen pendientes de desarrollo.

### Generar el alta inicial del administrador en tu equipo

Ejecutar en PowerShell desde la carpeta del proyecto: powershell -NoProfile -File .\scripts\create-admin-sql.ps1. Solicita nombre, correo y contraseña oculta con confirmación. Genera storage/app/private/admin-bootstrap.sql con hash bcrypt, para importar manualmente en phpMyAdmin después de las tablas. Solo inserta si no hay usuarios, sin cambiar cuentas existentes. No versionar ni publicar ese archivo. La contraseña nunca se envía por chat ni se pasa como argumento a un proceso.

