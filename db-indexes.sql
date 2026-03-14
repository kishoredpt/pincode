-- Phase 6: Recommended database indexes for scale (run once in production database)
ALTER TABLE post_offices ADD INDEX idx_pincode (pincode);
ALTER TABLE post_offices ADD INDEX idx_state_district (statename, district);
ALTER TABLE post_offices ADD INDEX idx_state_district_office (statename, district, officename);
ALTER TABLE post_offices ADD INDEX idx_slug (slug);
ALTER TABLE post_offices ADD INDEX idx_updated_at (updated_at);

ALTER TABLE articles ADD INDEX idx_articles_slug (slug);
ALTER TABLE articles ADD INDEX idx_articles_created_at (created_at);

-- Railway mapping indexes
ALTER TABLE railway_stations ADD INDEX idx_major_station (is_major, station_name);
ALTER TABLE pincode_nearest_railway_station ADD INDEX idx_pincode_distance (pincode, distance_km);

