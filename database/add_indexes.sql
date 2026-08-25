-- =============================================================
-- Guryo Samo — Performance Indexes Migration
-- Run this ONCE against your real_estate_db database.
-- Safe to run: all statements use IF NOT EXISTS.
-- =============================================================

-- properties: most searched/filtered columns
ALTER TABLE `properties`
    ADD INDEX IF NOT EXISTS `idx_status`     (`status`),
    ADD INDEX IF NOT EXISTS `idx_type`       (`type`),
    ADD INDEX IF NOT EXISTS `idx_price`      (`price`),
    ADD INDEX IF NOT EXISTS `idx_location`   (`location`(50)),
    ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);

-- messages: unread filter + date sort
ALTER TABLE `messages`
    ADD INDEX IF NOT EXISTS `idx_is_read`    (`is_read`),
    ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);

-- appointments: status + date lookups
ALTER TABLE `appointments`
    ADD INDEX IF NOT EXISTS `idx_status`       (`status`),
    ADD INDEX IF NOT EXISTS `idx_scheduled_at` (`scheduled_at`),
    ADD INDEX IF NOT EXISTS `idx_property_id`  (`property_id`);

-- users: login lookups by username/role
ALTER TABLE `users`
    ADD INDEX IF NOT EXISTS `idx_username` (`username`),
    ADD INDEX IF NOT EXISTS `idx_role`     (`role`);

-- construction_projects: status + name search
ALTER TABLE `construction_projects`
    ADD INDEX IF NOT EXISTS `idx_status` (`status`),
    ADD INDEX IF NOT EXISTS `idx_name`   (`name`(80));

-- construction_tasks: project + status
ALTER TABLE `construction_tasks`
    ADD INDEX IF NOT EXISTS `idx_project_id` (`project_id`),
    ADD INDEX IF NOT EXISTS `idx_status`     (`status`);

-- construction_materials: project filter
ALTER TABLE `construction_materials`
    ADD INDEX IF NOT EXISTS `idx_project_id` (`project_id`);

-- contractors: status filter
ALTER TABLE `contractors`
    ADD INDEX IF NOT EXISTS `idx_status` (`status`);

-- transactions: type + date range queries
ALTER TABLE `transactions`
    ADD INDEX IF NOT EXISTS `idx_type`       (`type`),
    ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);

SELECT 'Indexes applied successfully.' AS result;
