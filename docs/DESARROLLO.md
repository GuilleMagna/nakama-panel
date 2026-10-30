# Trabajo con Git

## Proyecto

- Carpeta: `C:\localhost\nakama-panel`.
- Rama principal: `main`.
- Documentación vigente: `docs/REQUERIMIENTOS.md`.
- Repositorio remoto público: https://github.com/GuilleMagna/nakama-panel.

## Flujo propuesto

1. Trabajar siempre sobre `main`, según lo indicado por el usuario. No crear otras ramas salvo pedido explícito.
2. Revisar cambios y ejecutar las verificaciones correspondientes antes de hacer commit.
3. Crear commits con descripciones claras y sin datos privados.
4. Publicar los cambios probados directamente en `main`.
5. Desplegar únicamente cambios revisados; publicar en Git no implica desplegar en producción.

No copiar archivos de entorno, bases de datos ni credenciales del ERP Agropecuario. Ambos proyectos son repositorios independientes.

Para mantener vigente la comparación de archivos del diagnóstico, después de preparar los cambios con `git add`, ejecutar `php scripts/export-tracked-files.php` y agregar `deploy/tracked-files.json` al mismo commit. El manifiesto contiene solo rutas y hashes de archivos versionados, no datos privados. Es una ayuda de diagnóstico y no reemplaza el estado de Git del servidor.

## Estado inicial

Repositorio local inicializado y documentación versionada. Todavía no hay aplicación instalada. El 29/10/2026 se creó el repositorio privado GuilleMagna/nakama-panel con la sesión autorizada de GitHub CLI.
