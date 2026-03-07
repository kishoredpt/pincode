CREATE TABLE IF NOT EXISTS states (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS districts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  state_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL,
  UNIQUE KEY uniq_district_state (state_id, slug),
  KEY idx_district_name (name),
  CONSTRAINT fk_district_state FOREIGN KEY (state_id) REFERENCES states(id)
);

CREATE TABLE IF NOT EXISTS areas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  district_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  UNIQUE KEY uniq_area_district (district_id, slug),
  KEY idx_area_name (name),
  CONSTRAINT fk_area_district FOREIGN KEY (district_id) REFERENCES districts(id)
);

CREATE TABLE IF NOT EXISTS pincodes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code CHAR(6) NOT NULL UNIQUE,
  district_id BIGINT UNSIGNED NOT NULL,
  state_id BIGINT UNSIGNED NOT NULL,
  KEY idx_pincode_district (district_id),
  KEY idx_pincode_state (state_id),
  CONSTRAINT fk_pincode_district FOREIGN KEY (district_id) REFERENCES districts(id),
  CONSTRAINT fk_pincode_state FOREIGN KEY (state_id) REFERENCES states(id)
);

CREATE TABLE IF NOT EXISTS post_offices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  office_name VARCHAR(180) NOT NULL,
  area_id BIGINT UNSIGNED NULL,
  pincode_id BIGINT UNSIGNED NOT NULL,
  delivery_status VARCHAR(30) DEFAULT NULL,
  division_name VARCHAR(120) DEFAULT NULL,
  region_name VARCHAR(120) DEFAULT NULL,
  KEY idx_postoffice_area (area_id),
  KEY idx_postoffice_pincode (pincode_id),
  KEY idx_postoffice_office (office_name),
  CONSTRAINT fk_postoffice_area FOREIGN KEY (area_id) REFERENCES areas(id),
  CONSTRAINT fk_postoffice_pincode FOREIGN KEY (pincode_id) REFERENCES pincodes(id)
);

CREATE TABLE IF NOT EXISTS blog_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  content MEDIUMTEXT NOT NULL,
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_blog_created_at (created_at)
);
