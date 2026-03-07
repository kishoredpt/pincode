ALTER TABLE post_offices
  ADD INDEX idx_po_pincode (pincode),
  ADD INDEX idx_po_district (district),
  ADD INDEX idx_po_state (statename),
  ADD INDEX idx_po_officename (officename),
  ADD INDEX idx_po_state_district_pin (statename, district, pincode);
