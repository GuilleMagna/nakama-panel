# Nakama Panel

Panel interno de Whileam para gestionar sitios WordPress, clientes, proveedores, servicios, ingresos, egresos y vencimientos.

Nombre de trabajo: **Nakama Panel**. MVP designa la primera entrega, no el nombre permanente del producto.

Destino de producción previsto: `panel.whileam.com.ar`, en reseller propio con cPanel.

## Estado

Repositorio inicial de planificación. Todavía no hay aplicación ejecutable ni dependencias instaladas. No se ha desplegado ni conectado a sitios reales.

- [Requerimientos](docs/REQUERIMIENTOS.md)
- [Propuesta técnica](docs/ARQUITECTURA.md)
- [Primeras entregas propuestas](docs/PLAN.md)
- [Trabajo con Git](docs/DESARROLLO.md)

Este repositorio contiene la copia de trabajo vigente de los requerimientos. El documento previo de `nakama-central` queda como referencia del relevamiento anterior.

## Tecnología recomendada

Laravel + Livewire + Blade + Tailwind CSS, con MySQL/MariaDB. Mantener la familia tecnológica del ERP Agropecuario, con versiones vigentes compatibles entre sí y con el hosting; no copiar su aplicación, credenciales ni datos.

Las versiones exactas se fijarán al preparar la aplicación y verificar PHP y extensiones en desarrollo y cPanel.

## Control de versiones

Repositorio Git local independiente con rama `main`. El remoto GitHub todavía no está creado ni configurado; se recomienda repositorio privado `nakama-panel`.

No incluir datos reales, tokens, credenciales, bases de datos ni copias de seguridad. Los ejemplos deben usar información ficticia.
