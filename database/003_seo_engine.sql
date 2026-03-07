ALTER TABLE pincodes
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE post_offices
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE areas
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE districts
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE states
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS seo_generation_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(191) NOT NULL,
    status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_seo_queue_entity (entity_type, entity_id),
    KEY idx_seo_queue_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(191) NOT NULL,
    page_path VARCHAR(255) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    html MEDIUMTEXT NOT NULL,
    meta_title VARCHAR(255) NOT NULL,
    meta_description VARCHAR(320) NOT NULL,
    canonical_url VARCHAR(255) NOT NULL,
    last_generated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_seo_pages_path (page_path),
    UNIQUE KEY uq_seo_pages_entity (entity_type, entity_id),
    KEY idx_seo_pages_last_generated (last_generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_internal_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_path VARCHAR(255) NOT NULL,
    target_path VARCHAR(255) NOT NULL,
    section_name VARCHAR(80) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_seo_links (source_path, target_path, section_name),
    KEY idx_seo_links_source (source_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
