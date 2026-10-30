-- Solo para instalaciones existentes que todavía NO tengan esta columna.
-- Seleccionar bizpills_panel en phpMyAdmin e importar una sola vez.
ALTER TABLE `sites` ADD `access_credentials` TEXT NULL;
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_30_000001_add_access_credentials_to_sites', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`;
