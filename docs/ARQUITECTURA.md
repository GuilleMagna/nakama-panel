# Propuesta técnica

Estado: recomendación inicial, 29/10/2026. Las versiones y capacidades concretas de producción están pendientes de comprobación.

## Base recomendada

- Laravel para reglas de negocio, autenticación, validaciones, integración HTTP, cifrado y tareas programadas.
- Blade y Livewire para tablas con filtros y paginación, formularios, fichas e historial interactivo.
- Tailwind CSS para un diseño propio y consistente.
- MySQL o MariaDB como base de datos, según servicio disponible en cPanel.
- Vite para compilar los recursos durante desarrollo o preparación del despliegue. No exigir un servidor Node permanente en producción.

Referencia local inspeccionada: el ERP Agropecuario declara Laravel ^11.44, Livewire ^3.5 y Tailwind ^3.1.0 en sus manifiestos. Se conserva el enfoque, no se fijan esas versiones para este proyecto nuevo.

## Aplicación y módulos

Una aplicación Laravel con módulos internos: clientes/sitios, monitoreo, accesos, historia, proveedores/planes, servicios/cuotas, movimientos y avisos. Una base de datos, inicialmente un administrador y sin registro público.

Una tabla interactiva cumple el requisito funcional de DataTable sin imponer todavía una biblioteca concreta. Priorizar filtros, orden y paginación ejecutados en el servidor.

Los saldos se calculan a partir de obligaciones y cobros aplicados. Cada pago real conserva su fecha e importe; ingresos/egresos nunca se generan por el mero alta de un contrato o cuota. Guardar importes con precisión decimal fija o centavos enteros, sin aritmética monetaria en coma flotante.

## Tareas periódicas

Programador de Laravel ejecutado por cron de cPanel. Monitoreo cada 12 horas por defecto con configuración global y por sitio; revisión económica diaria en America/Buenos_Aires.

Distribuir consultas y ejecutar trabajos cortos con límites de concurrencia, bloqueo por sitio y prevención de duplicados. Usar colas en base de datos si se necesitan; no depender inicialmente de Redis, Supervisor o procesos residentes. Confirmar que el hosting permite cron, ejecución PHP por consola, conexiones HTTPS salientes y tiempos suficientes. No llamar todos los sitios al abrir una pantalla.

## Despliegue

El subdominio debe apuntar a la carpeta pública de Laravel, manteniendo el código privado, archivos de entorno y secretos fuera de la raíz pública. Base de datos propia, HTTPS, debug desactivado y respaldos de base y claves de cifrado.

Comprobar PHP, extensiones, MySQL/MariaDB, rutas y límites reales antes de instalar y fijar dependencias. La documentación consultada de Laravel 13 requiere PHP >= 8.3; no se ha verificado todavía el hosting del usuario.

## Accesos y secretos

Tokens del monitor y contraseñas de WordPress/cPanel/FTP cifrados; revelado explícito y registro de acceso sin secretos. No colocar contraseñas reales en documentos, archivos de ejemplo ni commits. No copiar el entorno del ERP.

WhatsApp se implementará mediante un adaptador cuyo proveedor, credenciales y costos se elegirán después. Los canales pueden apagarse independientemente sin detener el control de vencimientos.

## Fuentes oficiales consultadas

- Despliegue y requisitos: https://laravel.com/framework/docs/deployment
- Programador de tareas: https://laravel.com/framework/docs/12.x/scheduling
- Livewire: https://livewire.laravel.com/docs/3.x/installation

Estas referencias orientan la arquitectura; fijar documentación y dependencias de una misma versión al comenzar la implementación.

## Base implementada en entrega 01

Laravel 12.70.2, Livewire 4.4.8, Tailwind 4.3.3 y Vite 7.3.7. PHP local 8.2.12. SQLite para desarrollo/pruebas; MySQL/MariaDB pendiente de validar en el hosting. pnpm como gestor de recursos. El alcance ejecutable está descrito en ENTREGA-01.md.
