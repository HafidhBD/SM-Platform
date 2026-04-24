-- ============================================================
-- Market Intelligence Platform - Database Schema
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- -----------------------------------------------------------
-- Users
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('admin','analyst','viewer') NOT NULL DEFAULT 'admin',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_username` (`username`),
    KEY `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Projects (Brands / Entities to monitor)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_projects_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Project Keywords
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_keywords` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL,
    `keyword` VARCHAR(255) NOT NULL,
    `type` ENUM('search','crisis','exclude') NOT NULL DEFAULT 'search',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pk_project` (`project_id`),
    KEY `idx_pk_type` (`type`),
    CONSTRAINT `fk_pk_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Project Competitors
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `competitors` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `username` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_comp_project` (`project_id`),
    CONSTRAINT `fk_comp_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Project Hashtags
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_hashtags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL,
    `hashtag` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ph_project` (`project_id`),
    CONSTRAINT `fk_ph_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Project Target Accounts
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_accounts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL,
    `account_username` VARCHAR(100) NOT NULL,
    `account_name` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pa_project` (`project_id`),
    CONSTRAINT `fk_pa_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Collection Runs (Apify scraping sessions)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `collection_runs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL,
    `actor_id` VARCHAR(255) NOT NULL DEFAULT 'scraply~x-twitter-posts-search',
    `run_id` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('pending','running','completed','failed','timeout') NOT NULL DEFAULT 'pending',
    `targets` TEXT DEFAULT NULL COMMENT 'JSON array of search targets',
    `input_config` TEXT DEFAULT NULL COMMENT 'JSON of Apify input config',
    `posts_found` INT UNSIGNED NOT NULL DEFAULT 0,
    `posts_stored` INT UNSIGNED NOT NULL DEFAULT 0,
    `error_message` TEXT DEFAULT NULL,
    `started_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cr_project` (`project_id`),
    KEY `idx_cr_status` (`status`),
    KEY `idx_cr_run_id` (`run_id`),
    CONSTRAINT `fk_cr_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Posts (from X/Twitter)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL,
    `collection_run_id` INT UNSIGNED DEFAULT NULL,
    `platform` VARCHAR(50) NOT NULL DEFAULT 'x_twitter',
    `external_post_id` VARCHAR(100) DEFAULT NULL,
    `post_url` VARCHAR(500) DEFAULT NULL,
    `author_name` VARCHAR(255) DEFAULT NULL,
    `author_username` VARCHAR(100) DEFAULT NULL,
    `author_followers` INT UNSIGNED DEFAULT NULL,
    `author_verified` TINYINT(1) DEFAULT 0,
    `author_bio` TEXT DEFAULT NULL,
    `content_text` TEXT NOT NULL,
    `posted_at` DATETIME DEFAULT NULL,
    `likes_count` INT UNSIGNED DEFAULT 0,
    `replies_count` INT UNSIGNED DEFAULT 0,
    `reposts_count` INT UNSIGNED DEFAULT 0,
    `quotes_count` INT UNSIGNED DEFAULT 0,
    `views_count` INT UNSIGNED DEFAULT 0,
    `bookmarks_count` INT UNSIGNED DEFAULT 0,
    `language` VARCHAR(10) DEFAULT NULL,
    `hashtags` TEXT DEFAULT NULL COMMENT 'JSON array',
    `tagged_users` TEXT DEFAULT NULL COMMENT 'JSON array',
    `photos` TEXT DEFAULT NULL COMMENT 'JSON array',
    `videos` TEXT DEFAULT NULL COMMENT 'JSON array',
    `raw_json` LONGTEXT DEFAULT NULL COMMENT 'Full raw API response',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_post_external` (`project_id`, `external_post_id`),
    KEY `idx_posts_project` (`project_id`),
    KEY `idx_posts_author` (`author_username`),
    KEY `idx_posts_date` (`posted_at`),
    KEY `idx_posts_platform` (`platform`),
    CONSTRAINT `fk_posts_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_posts_run` FOREIGN KEY (`collection_run_id`) REFERENCES `collection_runs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Post AI Analysis
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `post_ai_analysis` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `post_id` INT UNSIGNED NOT NULL,
    `sentiment` ENUM('positive','negative','neutral') DEFAULT NULL,
    `sentiment_score` DECIMAL(5,2) DEFAULT NULL COMMENT '0-100, 50=neutral',
    `reputation_label` ENUM('praise','complaint','attack','sarcasm','inquiry','rumor','escalation','neutral_mention','other') DEFAULT NULL,
    `crisis_flag` TINYINT(1) NOT NULL DEFAULT 0,
    `attack_flag` TINYINT(1) NOT NULL DEFAULT 0,
    `complaint_flag` TINYINT(1) NOT NULL DEFAULT 0,
    `sarcasm_flag` TINYINT(1) NOT NULL DEFAULT 0,
    `topic_label` VARCHAR(100) DEFAULT NULL,
    `risk_score` TINYINT UNSIGNED DEFAULT 0 COMMENT '0-100',
    `ai_summary` TEXT DEFAULT NULL,
    `ai_keywords` TEXT DEFAULT NULL COMMENT 'JSON array of extracted keywords',
    `analysis_model` VARCHAR(100) DEFAULT NULL,
    `analysis_batch_id` VARCHAR(50) DEFAULT NULL,
    `analyzed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_analysis_post` (`post_id`),
    KEY `idx_aa_sentiment` (`sentiment`),
    KEY `idx_aa_reputation` (`reputation_label`),
    KEY `idx_aa_crisis` (`crisis_flag`),
    KEY `idx_aa_topic` (`topic_label`),
    KEY `idx_aa_risk` (`risk_score`),
    CONSTRAINT `fk_aa_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Alerts
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `alerts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL,
    `alert_type` ENUM('negative_spike','attack_detected','complaint_surge','crisis_keyword','volume_spike','rumor_detected','reputation_threat','custom') NOT NULL,
    `severity` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    `title` VARCHAR(500) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `evidence` TEXT DEFAULT NULL COMMENT 'JSON with supporting data',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `is_resolved` TINYINT(1) NOT NULL DEFAULT 0,
    `resolved_at` DATETIME DEFAULT NULL,
    `resolved_by` INT UNSIGNED DEFAULT NULL,
    `triggered_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_alerts_project` (`project_id`),
    KEY `idx_alerts_type` (`alert_type`),
    KEY `idx_alerts_severity` (`severity`),
    KEY `idx_alerts_read` (`is_read`),
    CONSTRAINT `fk_alerts_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- AI Summaries (Executive summaries)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ai_summaries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT UNSIGNED NOT NULL,
    `summary_type` ENUM('daily','weekly','manual','crisis') NOT NULL DEFAULT 'manual',
    `period_start` DATE DEFAULT NULL,
    `period_end` DATE DEFAULT NULL,
    `executive_summary` TEXT DEFAULT NULL,
    `top_negative_points` TEXT DEFAULT NULL COMMENT 'JSON array',
    `top_positive_points` TEXT DEFAULT NULL COMMENT 'JSON array',
    `recommendations` TEXT DEFAULT NULL COMMENT 'JSON array',
    `campaign_opportunities` TEXT DEFAULT NULL COMMENT 'JSON array',
    `audience_interests` TEXT DEFAULT NULL COMMENT 'JSON array',
    `repeated_messages` TEXT DEFAULT NULL COMMENT 'JSON array',
    `market_gaps` TEXT DEFAULT NULL COMMENT 'JSON array',
    `reputation_status` ENUM('excellent','good','neutral','concerning','critical') DEFAULT NULL,
    `analysis_model` VARCHAR(100) DEFAULT NULL,
    `posts_analyzed` INT UNSIGNED DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_as_project` (`project_id`),
    KEY `idx_as_type` (`summary_type`),
    KEY `idx_as_period` (`period_start`, `period_end`),
    CONSTRAINT `fk_as_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Settings
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `description` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`setting_key`),
    KEY `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Logs
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(50) NOT NULL DEFAULT 'general',
    `message` TEXT NOT NULL,
    `context` TEXT DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_logs_type` (`type`),
    KEY `idx_logs_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Default Settings Data
-- -----------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `description`) VALUES
('apify_api_token', '', 'apify', 'Apify API Token'),
('apify_actor_id', 'scraply~x-twitter-posts-search', 'apify', 'Apify Actor ID'),
('apify_max_tweets', '50', 'apify', 'Default max tweets per target'),
('apify_time_window', '7', 'apify', 'Default time window in days'),
('apify_search_type', 'latest', 'apify', 'Default search type: top or latest'),
('apify_use_proxy', '1', 'apify', 'Use Apify proxy'),
('openai_api_key', '', 'openai', 'OpenAI API Key'),
('openai_model', 'gpt-4o-mini', 'openai', 'OpenAI Model name'),
('openai_batch_size', '20', 'openai', 'Posts per analysis batch'),
('alert_negative_threshold', '40', 'alerts', 'Negative sentiment % threshold'),
('alert_volume_spike_percent', '50', 'alerts', 'Volume spike % threshold'),
('alert_crisis_keyword_threshold', '5', 'alerts', 'Crisis keyword count threshold'),
('alert_attack_post_threshold', '10', 'alerts', 'Attack-flagged post count threshold'),
('system_language', 'ar', 'general', 'Default system language'),
('system_timezone', 'Asia/Riyadh', 'general', 'System timezone'),
('system_name', 'Market Intelligence Platform', 'general', 'Platform name');

SET FOREIGN_KEY_CHECKS = 1;
