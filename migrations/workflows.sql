CREATE TABLE `ilab_workflows` (
                               `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                               `workflow_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `post_id` bigint unsigned not null,
                               `status` bigint not null default 0,
                               `state` text COLLATE utf8mb4_unicode_ci,
                               `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               PRIMARY KEY (`id`),
                               FOREIGN KEY (`post_id`) REFERENCES wp_posts(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
