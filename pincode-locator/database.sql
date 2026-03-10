CREATE DATABASE IF NOT EXISTS pincode_locator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pincode_locator;

CREATE TABLE states (
  state_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  state_name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE districts (
  district_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  district_name VARCHAR(140) NOT NULL,
  state_id INT UNSIGNED NOT NULL,
  slug VARCHAR(160) NOT NULL,
  UNIQUE KEY uq_district_state (district_name, state_id),
  KEY idx_district_state (state_id),
  CONSTRAINT fk_district_state FOREIGN KEY (state_id) REFERENCES states(state_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE post_offices (
  post_office_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  office_name VARCHAR(180) NOT NULL,
  pincode CHAR(6) NOT NULL,
  district_id INT UNSIGNED NOT NULL,
  state_id INT UNSIGNED NOT NULL,
  delivery_status VARCHAR(60) NOT NULL,
  office_type VARCHAR(40) NOT NULL,
  division VARCHAR(120) DEFAULT NULL,
  region VARCHAR(120) DEFAULT NULL,
  circle VARCHAR(120) DEFAULT NULL,
  latitude DECIMAL(10,7) DEFAULT NULL,
  longitude DECIMAL(10,7) DEFAULT NULL,
  KEY idx_pincode (pincode),
  KEY idx_state (state_id),
  KEY idx_district (district_id),
  KEY idx_po_lookup (pincode,office_name),
  CONSTRAINT fk_po_district FOREIGN KEY (district_id) REFERENCES districts(district_id) ON DELETE CASCADE,
  CONSTRAINT fk_po_state FOREIGN KEY (state_id) REFERENCES states(state_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE articles (
  article_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  content LONGTEXT NOT NULL,
  meta_title VARCHAR(255) NOT NULL,
  meta_description VARCHAR(320) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO states(state_name,slug) VALUES
('Telangana','telangana'),('Maharashtra','maharashtra'),('Karnataka','karnataka');

INSERT INTO districts(district_name,state_id,slug) VALUES
('Hyderabad',1,'hyderabad'),('Rangareddy',1,'rangareddy'),('Mumbai',2,'mumbai');

INSERT INTO post_offices(office_name,pincode,district_id,state_id,delivery_status,office_type,division,region,circle,latitude,longitude) VALUES
('Abids Post Office','500001',1,1,'Delivery','HO','Hyderabad City','Hyderabad','Telangana',17.3910,78.4760),
('Gachibowli','500032',2,1,'Delivery','SO','Hyderabad City','Hyderabad','Telangana',17.4401,78.3489),
('Mumbai GPO','400001',3,2,'Delivery','HO','Mumbai City','Mumbai','Maharashtra',18.9388,72.8354);

-- Import approx 155000 records from CSV (recommended for production):
-- LOAD DATA LOCAL INFILE '/path/to/india_post_offices_155k.csv'
-- INTO TABLE post_offices
-- FIELDS TERMINATED BY ',' ENCLOSED BY '"'
-- LINES TERMINATED BY '\n'
-- IGNORE 1 LINES
-- (office_name,pincode,@district_name,@state_name,delivery_status,office_type,division,region,circle,latitude,longitude)
-- SET district_id = (SELECT district_id FROM districts WHERE district_name=@district_name LIMIT 1),
--     state_id    = (SELECT state_id FROM states WHERE state_name=@state_name LIMIT 1);
