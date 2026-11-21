-- =====================================================================
-- Harvestly database-level validation constraints
-- ---------------------------------------------------------------------
-- The application already validates every write in PHP, but the database
-- must not rely on that alone. These CHECK constraints are the last line of
-- defence: even a direct SQL statement, a bug or a tampered request cannot
-- store a negative price, a non-positive quantity, a rating outside 1-5 or an
-- inverted min/max range.
--
-- Requires strict SQL mode (STRICT_TRANS_TABLES) for the length limits to be
-- enforced as errors rather than silent truncation. See mysql/bin/my.ini.
--
-- Safe to run more than once: MariaDB has no "ADD CONSTRAINT IF NOT EXISTS",
-- so re-running reports an error for the constraints that already exist.
-- =====================================================================

-- ---------------------------------------------------------------------
-- Quantities that are always strictly positive (a line of business)
-- ---------------------------------------------------------------------
ALTER TABLE cart_items
  ADD CONSTRAINT chk_cart_items_quantity_positive CHECK (quantity > 0);

ALTER TABLE preorders
  ADD CONSTRAINT chk_preorders_quantity_positive CHECK (quantity > 0);

ALTER TABLE order_items
  ADD CONSTRAINT chk_order_items_quantity_positive CHECK (quantity > 0);

-- ---------------------------------------------------------------------
-- Money may be zero but never negative
-- ---------------------------------------------------------------------
ALTER TABLE products
  ADD CONSTRAINT chk_products_unit_price_non_negative CHECK (unit_price >= 0);

ALTER TABLE products
  ADD CONSTRAINT chk_products_available_quantity_non_negative CHECK (available_quantity >= 0);

ALTER TABLE order_items
  ADD CONSTRAINT chk_order_items_price_non_negative CHECK (unit_price_snapshot >= 0);

ALTER TABLE order_items
  ADD CONSTRAINT chk_order_items_line_total_non_negative CHECK (line_total >= 0);

ALTER TABLE orders
  ADD CONSTRAINT chk_orders_subtotal_non_negative CHECK (product_subtotal >= 0);

ALTER TABLE orders
  ADD CONSTRAINT chk_orders_farmer_fee_non_negative CHECK (farmer_marketplace_fee >= 0);

ALTER TABLE orders
  ADD CONSTRAINT chk_orders_buyer_fee_non_negative CHECK (buyer_service_fee >= 0);

ALTER TABLE orders
  ADD CONSTRAINT chk_orders_delivery_fee_non_negative CHECK (delivery_fee >= 0);

ALTER TABLE orders
  ADD CONSTRAINT chk_orders_grand_total_non_negative CHECK (grand_total >= 0);

ALTER TABLE payments
  ADD CONSTRAINT chk_payments_amount_non_negative CHECK (amount >= 0);

ALTER TABLE earnings
  ADD CONSTRAINT chk_earnings_amount_non_negative CHECK (amount >= 0);

ALTER TABLE settlements
  ADD CONSTRAINT chk_settlements_total_non_negative CHECK (total_amount >= 0);

ALTER TABLE district_distances
  ADD CONSTRAINT chk_district_distances_non_negative CHECK (distance_km >= 0);

-- ---------------------------------------------------------------------
-- Ratings are a 1-5 scale
-- ---------------------------------------------------------------------
ALTER TABLE reviews
  ADD CONSTRAINT chk_reviews_rating_range CHECK (rating >= 1 AND rating <= 5);

-- ---------------------------------------------------------------------
-- Boolean flags may only be 0 or 1
-- ---------------------------------------------------------------------
ALTER TABLE districts
  ADD CONSTRAINT chk_districts_is_active CHECK (is_active IN (0, 1));

ALTER TABLE product_categories
  ADD CONSTRAINT chk_product_categories_is_active CHECK (is_active IN (0, 1));

ALTER TABLE shelf_life_references
  ADD CONSTRAINT chk_shelf_life_is_active CHECK (is_active IN (0, 1));

ALTER TABLE courier_coverage_routes
  ADD CONSTRAINT chk_courier_routes_is_active CHECK (is_active IN (0, 1));

ALTER TABLE notifications
  ADD CONSTRAINT chk_notifications_is_read CHECK (is_read IN (0, 1));

ALTER TABLE product_images
  ADD CONSTRAINT chk_product_images_is_primary CHECK (is_primary IN (0, 1));

-- ---------------------------------------------------------------------
-- Storage ranges must be ordered (min <= max). NULLs are allowed and pass.
-- ---------------------------------------------------------------------
ALTER TABLE shelf_life_references
  ADD CONSTRAINT chk_shelf_life_temperature_range
    CHECK (temperature_min_c IS NULL OR temperature_max_c IS NULL
           OR temperature_min_c <= temperature_max_c);

ALTER TABLE shelf_life_references
  ADD CONSTRAINT chk_shelf_life_humidity_range
    CHECK (humidity_min_percent IS NULL OR humidity_max_percent IS NULL
           OR humidity_min_percent <= humidity_max_percent);

ALTER TABLE shelf_life_references
  ADD CONSTRAINT chk_shelf_life_days_range
    CHECK (storage_life_min_days IS NULL OR storage_life_max_days IS NULL
           OR storage_life_min_days <= storage_life_max_days);
