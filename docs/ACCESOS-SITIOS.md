# Accesos por sitio

El alta y la edición permiten guardar credenciales de WordPress, cPanel y FTP/FTPS/SFTP.
Son opcionales y se almacenan juntas cifradas mediante APP_KEY. Usuarios y contraseñas se muestran como texto en la edición autenticada y cada campo tiene un botón para copiar su valor actual. La respuesta de edición impide su almacenamiento en caché. Las credenciales no se incluyen en la serialización del sitio ni en los datos de sesión de una validación fallida.
Al editar, una contraseña vacía conserva la anterior; una nueva la reemplaza. El botón de copiar informa éxito, campo vacío o fallo de permisos del portapapeles, y nunca envía el formulario.

## Actualizar una instalación existente

Respaldar la base. En Git de cPanel pulsar **Actualizar desde remoto** y luego **Deploy HEAD Commit**. El archivo `.cpanel.yml` ejecuta `deploy/cpanel.sh`, que aplica las migraciones pendientes y limpia las vistas compiladas. Laravel registra cada migración aplicada: repetir el despliegue no repite los cambios. No cambiar APP_KEY: protege también las credenciales guardadas.

El archivo `deploy/update-site-access.sql` queda solo como alternativa para hosting sin ejecución de tareas de despliegue. No se importa si se utiliza el despliegue con migraciones. Si ya se importó correctamente, su registro en `migrations` evita que Laravel vuelva a aplicar ese cambio.

En instalaciones con consola se utiliza `php artisan migrate --force` en lugar del SQL manual. Localmente, `php artisan migrate`.
