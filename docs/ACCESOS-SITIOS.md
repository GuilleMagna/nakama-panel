# Accesos por sitio

El alta y la edición permiten guardar credenciales de WordPress, cPanel y FTP/FTPS/SFTP.
Son opcionales y se almacenan juntas cifradas mediante APP_KEY. Las contraseñas nunca se precargan en el formulario ni se incluyen en la serialización del sitio o en los datos de sesión de una validación fallida.
Al editar, una contraseña vacía conserva la anterior; una nueva la reemplaza. Esta entrega permite registrar y reemplazar accesos, sin revelar contraseñas guardadas.

## Actualizar una instalación existente

Respaldar la base. Antes de publicar el código nuevo, importar una sola vez `deploy/update-site-access.sql` en la base seleccionada en phpMyAdmin. No importar el esquema inicial sobre una instalación existente. Luego actualizar la rama del repositorio en cPanel. No cambiar APP_KEY: protege también las credenciales guardadas.

En instalaciones con consola se utiliza `php artisan migrate --force` en lugar del SQL manual. Localmente, `php artisan migrate`.
