# Entrega 01 — Base privada, clientes y sitios

29/10/2026.

## Implementado

- Laravel 12, Livewire 4, Tailwind 4 y Vite, con dependencias bloqueadas.
- Login privado, cierre de sesión, protección CSRF y límites de intentos. Sin registro público ni administrador con contraseña predeterminada.
- Alta y edición de clientes y sitios; sitios propios o de clientes y estados activo/pausado/archivado.
- Tabla interactiva de sitios con búsqueda, filtros por cliente/estado y paginación.
- Ficha del sitio, URL del backend y token del monitor cifrado, oculto en serialización y formularios. Editar sin token conserva el existente; errores de validación no guardan el token como entrada anterior.
- Registro de hechos históricos con fecha de ocurrencia, categoría, título, descripción y fecha aproximada opcional. Orden cronológico inverso.
- Intervalo global inicial de 12 horas con excepción por sitio y posibilidad de volver a heredar al dejar el campo vacío.
- Resumen con cantidades reales y estados vacíos, sin clientes ni sitios de demostración en la base local.

## Todavía pendiente

Consultas reales a Nakama Monitor y programación; indicadores técnicos de versiones/backups/seguridad; contraseñas de backend/cPanel/FTP; edición y resolución de incidentes; servicios, proveedores, cobros, cuotas y egresos; avisos y WhatsApp. Guardar un intervalo o token en esta entrega no ejecuta consultas.

No se realizó despliegue en cPanel ni pruebas con credenciales o sitios reales. SQLite se usa para desarrollo y pruebas; producción requiere configurar y validar MySQL/MariaDB.

## Verificación

Pruebas funcionales de acceso, autenticación, limitación de intentos, CRUD, cifrado, exclusión de secretos de sesión/HTML, validación de URLs, duplicados, historia, filtros e intervalos. Compilación de recursos visuales para producción.

Resultado local: 15 pruebas aprobadas con 89 comprobaciones; compilación Vite y caché de vistas correctas. Revisión en navegador de login, resumen, filtro Livewire y diseño móvil (390 px). Usuario temporal de revisión eliminado; la base local queda vacía. MySQL/cPanel y CI remoto todavía no verificados en este registro.
