-- Nakama Panel: instalación inicial para MySQL/MariaDB.
-- Importar únicamente en una base VACÍA seleccionada en phpMyAdmin.
-- Sin datos privados, usuarios, contraseñas ni instrucciones de borrado.
-- No usar este archivo para actualizar instalaciones existentes.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

create table `migrations` (`id` int unsigned not null auto_increment primary key, `migration` varchar(255) not null, `batch` int not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

create table `users` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `email` varchar(255) not null, `email_verified_at` timestamp null, `password` varchar(255) not null, `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

alter table `users` add unique `users_email_unique`(`email`);

create table `password_reset_tokens` (`email` varchar(255) not null, `token` varchar(255) not null, `created_at` timestamp null, primary key (`email`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

create table `sessions` (`id` varchar(255) not null, `user_id` bigint unsigned null, `ip_address` varchar(45) null, `user_agent` text null, `payload` longtext not null, `last_activity` int not null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

alter table `sessions` add index `sessions_user_id_index`(`user_id`);

alter table `sessions` add index `sessions_last_activity_index`(`last_activity`);

create table `cache` (`key` varchar(255) not null, `value` mediumtext not null, `expiration` int not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

alter table `cache` add index `cache_expiration_index`(`expiration`);

create table `cache_locks` (`key` varchar(255) not null, `owner` varchar(255) not null, `expiration` int not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

alter table `cache_locks` add index `cache_locks_expiration_index`(`expiration`);

create table `jobs` (`id` bigint unsigned not null auto_increment primary key, `queue` varchar(255) not null, `payload` longtext not null, `attempts` tinyint unsigned not null, `reserved_at` int unsigned null, `available_at` int unsigned not null, `created_at` int unsigned not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

alter table `jobs` add index `jobs_queue_index`(`queue`);

create table `job_batches` (`id` varchar(255) not null, `name` varchar(255) not null, `total_jobs` int not null, `pending_jobs` int not null, `failed_jobs` int not null, `failed_job_ids` longtext not null, `options` mediumtext null, `cancelled_at` int null, `created_at` int not null, `finished_at` int null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

create table `failed_jobs` (`id` bigint unsigned not null auto_increment primary key, `uuid` varchar(255) not null, `connection` text not null, `queue` text not null, `payload` longtext not null, `exception` longtext not null, `failed_at` timestamp not null default CURRENT_TIMESTAMP) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

alter table `failed_jobs` add unique `failed_jobs_uuid_unique`(`uuid`);

create table `clients` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `email` varchar(255) null, `phone` varchar(60) null, `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

create table `sites` (`id` bigint unsigned not null auto_increment primary key, `client_id` bigint unsigned null, `name` varchar(255) not null, `url` varchar(500) not null, `ownership` varchar(20) not null default 'client', `status` varchar(20) not null default 'active', `admin_url` varchar(500) null, `monitor_endpoint` varchar(500) null, `monitor_token` text null, `monitor_interval_hours` int unsigned null, `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

alter table `sites` add constraint `sites_client_id_foreign` foreign key (`client_id`) references `clients` (`id`) on delete restrict;

alter table `sites` add unique `sites_url_unique`(`url`);

create table `site_events` (`id` bigint unsigned not null auto_increment primary key, `site_id` bigint unsigned not null, `user_id` bigint unsigned not null, `category` varchar(30) not null, `title` varchar(255) not null, `occurred_on` date not null, `approximate` tinyint(1) not null default '0', `description` text not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

alter table `site_events` add constraint `site_events_site_id_foreign` foreign key (`site_id`) references `sites` (`id`) on delete restrict;

alter table `site_events` add constraint `site_events_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete restrict;

alter table `site_events` add index `site_events_site_id_occurred_on_index`(`site_id`, `occurred_on`);

create table `panel_settings` (`id` bigint unsigned not null auto_increment primary key, `monitor_interval_hours` int unsigned not null default '12', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

alter table `sites` add `access_credentials` text null;

INSERT INTO `migrations` (`migration`, `batch`) VALUES ('0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_10_29_000001_create_panel_tables', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_10_30_000001_add_access_credentials_to_sites', 1);
