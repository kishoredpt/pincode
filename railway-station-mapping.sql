-- Railway station mapping schema for PIN code nearest-station lookup.
-- Compatible with existing MySQL/MariaDB setup used by pincode project.

CREATE TABLE IF NOT EXISTS railway_stations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    station_code VARCHAR(10) NOT NULL,
    station_name VARCHAR(180) NOT NULL,
    state_name VARCHAR(120) DEFAULT NULL,
    district_name VARCHAR(120) DEFAULT NULL,
    latitude DECIMAL(10,7) NOT NULL,
    longitude DECIMAL(10,7) NOT NULL,
    is_major TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_station_code (station_code),
    KEY idx_station_name (station_name),
    KEY idx_station_state_district (state_name, district_name),
    KEY idx_station_geo (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pincode_nearest_railway_station (
    pincode CHAR(6) NOT NULL,
    station_id BIGINT UNSIGNED NOT NULL,
    distance_km DECIMAL(7,2) NOT NULL,
    distance_source VARCHAR(40) NOT NULL DEFAULT 'haversine',
    mapped_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (pincode),
    KEY idx_station_id (station_id),
    KEY idx_distance_km (distance_km),
    CONSTRAINT fk_pincode_nearest_station
        FOREIGN KEY (station_id) REFERENCES railway_stations(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional helper table to store all considered distance pairs while preparing data.
CREATE TABLE IF NOT EXISTS pincode_station_distance_cache (
    pincode CHAR(6) NOT NULL,
    station_id BIGINT UNSIGNED NOT NULL,
    distance_km DECIMAL(7,2) NOT NULL,
    calculated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (pincode, station_id),
    KEY idx_psdc_station (station_id),
    KEY idx_psdc_distance (distance_km),
    CONSTRAINT fk_psdc_station
        FOREIGN KEY (station_id) REFERENCES railway_stations(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example query to populate nearest station mapping from post_offices lat/long.
-- Run in batches for production-sized data (~1.6 lakh PINs).
-- INSERT INTO pincode_nearest_railway_station (pincode, station_id, distance_km, distance_source)
-- SELECT x.pincode, x.station_id, x.distance_km, 'haversine'
-- FROM (
--   SELECT po.pincode,
--          rs.id AS station_id,
--          ROUND(6371 * ACOS(
--              COS(RADIANS(po.latitude)) * COS(RADIANS(rs.latitude)) * COS(RADIANS(rs.longitude) - RADIANS(po.longitude)) +
--              SIN(RADIANS(po.latitude)) * SIN(RADIANS(rs.latitude))
--          ), 2) AS distance_km,
--          ROW_NUMBER() OVER (
--              PARTITION BY po.pincode
--              ORDER BY 6371 * ACOS(
--                  COS(RADIANS(po.latitude)) * COS(RADIANS(rs.latitude)) * COS(RADIANS(rs.longitude) - RADIANS(po.longitude)) +
--                  SIN(RADIANS(po.latitude)) * SIN(RADIANS(rs.latitude))
--              )
--          ) AS rn
--   FROM post_offices po
--   JOIN railway_stations rs
--   WHERE po.latitude IS NOT NULL
--     AND po.longitude IS NOT NULL
-- ) x
-- WHERE x.rn = 1
-- ON DUPLICATE KEY UPDATE
--   station_id = VALUES(station_id),
--   distance_km = VALUES(distance_km),
--   distance_source = VALUES(distance_source),
--   mapped_at = CURRENT_TIMESTAMP;
