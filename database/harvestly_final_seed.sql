-- Harvestly final sample data aligned with the revised in-scope system.
USE harvestly;
SET NAMES utf8mb4;
START TRANSACTION;


-- Demo password for all seeded users: testpass123


INSERT INTO users (user_id, role, full_name, email, password_hash, phone, account_status, last_login_at) VALUES
(1, 'ADMIN', 'System Administrator', 'admin@gmail.com', '$2y$10$JAWAea3aZt3m6ea/YZ82teE7v1FEjkse1.TdtAqI5xEY6cabEgdO2', '+94 11 234 5678', 'ACTIVE', '2026-09-24 18:00:00'),
(2, 'BUYER', 'Kasun Jayasinghe', 'buyer@gmail.com', '$2y$10$JAWAea3aZt3m6ea/YZ82teE7v1FEjkse1.TdtAqI5xEY6cabEgdO2', '+94 77 888 9999', 'ACTIVE', '2026-09-24 17:30:00'),
(3, 'FARMER', 'Sunil Perera', 'farmer@gmail.com', '$2y$10$JAWAea3aZt3m6ea/YZ82teE7v1FEjkse1.TdtAqI5xEY6cabEgdO2', '+94 77 123 4567', 'ACTIVE', '2026-09-24 16:45:00'),
(4, 'FARMER', 'Rohan Fernando', 'rohan@farm.lk', '$2y$10$JAWAea3aZt3m6ea/YZ82teE7v1FEjkse1.TdtAqI5xEY6cabEgdO2', '+94 76 345 6789', 'PENDING', NULL),
(5, 'COURIER_PARTNER', 'Lanka Agro Logistics', 'courier@gmail.com', '$2y$10$JAWAea3aZt3m6ea/YZ82teE7v1FEjkse1.TdtAqI5xEY6cabEgdO2', '+94 11 234 9999', 'ACTIVE', '2026-09-24 15:20:00'),
(6, 'COURIER_PARTNER', 'Ceylon Fresh Transit', 'info@ceylontransit.lk', '$2y$10$JAWAea3aZt3m6ea/YZ82teE7v1FEjkse1.TdtAqI5xEY6cabEgdO2', '+94 81 445 6789', 'PENDING', NULL),
(7, 'BUYER', 'Tharushi Weerasinghe', 'tharushi@harvestly.lk', '$2y$10$JAWAea3aZt3m6ea/YZ82teE7v1FEjkse1.TdtAqI5xEY6cabEgdO2', '+94 71 234 1188', 'ACTIVE', '2026-09-24 14:10:00'),
(8, 'FARMER', 'Dambulla Green Fields', 'farm@dambullagreen.lk', '$2y$10$JAWAea3aZt3m6ea/YZ82teE7v1FEjkse1.TdtAqI5xEY6cabEgdO2', '+94 75 345 8800', 'ACTIVE', '2026-09-24 13:40:00'),
(9, 'COURIER_PARTNER', 'Southern Fresh Routes', 'dispatch@southernfresh.lk', '$2y$10$JAWAea3aZt3m6ea/YZ82teE7v1FEjkse1.TdtAqI5xEY6cabEgdO2', '+94 91 555 6677', 'ACTIVE', '2026-09-24 12:15:00');

INSERT INTO buyer_profiles (buyer_id, default_address_line1, default_address_line2, default_city_town, default_postal_code, default_district_id) VALUES
(2, '12 Rajagiriya Road', NULL, 'Colombo 08', '00800', 1),
(7, '45 Lake View Avenue', NULL, 'Kandy', '20000', 4);

INSERT INTO farmer_profiles (farmer_id, farm_name, pickup_address_line1, pickup_address_line2, pickup_city_town, pickup_postal_code, district_id, verification_status) VALUES
(3, 'Sunlight Highlands Farm', 'Moon Plains', NULL, 'Nuwara Eliya', '22200', 6, 'APPROVED'),
(4, 'Vadamarachchi Orchards', 'Point Pedro Road', NULL, 'Jaffna', '40000', 10, 'PENDING'),
(8, 'Dambulla Green Fields', 'Inamaluwa Road', NULL, 'Dambulla', '21124', 5, 'APPROVED');

INSERT INTO courier_partner_profiles (courier_partner_id, organisation_name, contact_person_name, office_address_line1, office_address_line2, office_city_town, office_postal_code, office_district_id, availability_status, verification_status) VALUES
(5, 'Lanka Agro Logistics Pvt Ltd', 'Nimal Silva', '120 Logistics Avenue', NULL, 'Colombo', '01000', 1, 'AVAILABLE', 'APPROVED'),
(6, 'Ceylon Fresh Transit Co.', 'Dinesh Wijesinghe', '18 Transport Lane', NULL, 'Nuwara Eliya', '22200', 6, 'UNAVAILABLE', 'PENDING'),
(9, 'Southern Fresh Routes', 'Sahan de Silva', '88 Harbour Road', NULL, 'Galle', '80000', 7, 'AVAILABLE', 'APPROVED');

INSERT INTO password_reset_tokens (reset_id, user_id, token_hash, expires_at, used_at) VALUES
(1, 2, 'demo_used_reset_token_hash_not_valid_for_login', '2026-09-01 10:00:00', '2026-09-01 09:55:00');

INSERT INTO verification_documents (document_id, user_id, document_type, original_file_name, stored_file_path, status, reviewed_by, reviewed_at) VALUES
(1, 3, 'Supporting Verification Document', 'farmer_sunil_verification.jpg', 'assets/documents/images (12)_ab6deb5750b7909b.jpg', 'APPROVED', 1, '2026-08-28 10:00:00'),
(2, 4, 'Supporting Verification Document', 'farmer_rohan_verification.jpg', 'assets/documents/images (12)_ab6deb5750b7909b.jpg', 'PENDING', NULL, NULL),
(3, 5, 'Business Verification Document', 'lanka_agro_verification.jpg', 'assets/documents/images (12)_ab6deb5750b7909b.jpg', 'APPROVED', 1, '2026-08-28 10:10:00'),
(4, 6, 'Business Verification Document', 'ceylon_fresh_verification.jpg', 'assets/documents/images (12)_ab6deb5750b7909b.jpg', 'PENDING', NULL, NULL),
(5, 8, 'Supporting Verification Document', 'dambulla_green_verification.jpg', 'assets/documents/images (12)_ab6deb5750b7909b.jpg', 'APPROVED', 1, '2026-08-29 09:00:00'),
(6, 9, 'Business Verification Document', 'southern_routes_verification.jpg', 'assets/documents/images (12)_ab6deb5750b7909b.jpg', 'APPROVED', 1, '2026-08-29 09:15:00');

INSERT INTO courier_coverage_routes (route_id, courier_partner_id, origin_district_id, destination_district_id, is_active) VALUES
(1, 5, 6, 1, 1),
(2, 5, 6, 4, 1),
(3, 5, 5, 4, 1),
(4, 5, 5, 1, 1),
(5, 9, 7, 1, 1),
(6, 9, 7, 4, 1);

INSERT INTO product_categories (category_id, category_name, description, is_active) VALUES
(1, 'Vegetables', 'Fresh locally grown vegetables.', 1),
(2, 'Fruits', 'Fresh and seasonal Sri Lankan fruits.', 1),
(3, 'Leafy Greens', 'Fresh leafy vegetables and greens.', 1),
(4, 'Herbs & Spices', 'Culinary herbs, spices and related agricultural produce.', 1),
(5, 'Other Agricultural Produce', 'Other eligible agricultural produce not covered by the main categories.', 1);

INSERT INTO shelf_life_references (shelf_life_reference_id, product_reference_name, temperature_min_c, temperature_max_c, humidity_min_percent, humidity_max_percent, storage_life_min_days, storage_life_max_days, storage_guidance, source_reference, is_active) VALUES
(1, 'Avocado', 3.00, 13.00, 85.00, 90.00, 14, 56, 'Recommended storage: 3-13 C, 85-90% relative humidity, 14-56 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(2, 'Banana - Plantain', 13.00, 15.00, 90.00, 95.00, 7, 28, 'Recommended storage: 13-15 C, 90-95% relative humidity, 7-28 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(3, 'Beet (topped)', 0.00, 0.00, 98.00, 100.00, 120, 180, 'Recommended storage: 0 C, 98-100% relative humidity, 120-180 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(4, 'Chinese cabbage', 0.00, 0.00, 95.00, 100.00, 60, 90, 'Recommended storage: 0 C, 95-100% relative humidity, 60-90 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(5, 'Eggplant', 8.00, 12.00, 90.00, 95.00, 7, 7, 'Recommended storage: 8-12 C, 90-95% relative humidity, 7 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(6, 'Ginger', 13.00, 13.00, 65.00, 65.00, 180, 180, 'Recommended storage: 13 C, 65% relative humidity, 180 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(7, 'Guava', 5.00, 10.00, 90.00, 90.00, 14, 21, 'Recommended storage: 5-10 C, 90% relative humidity, 14-21 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(8, 'Leek', 0.00, 0.00, 95.00, 100.00, 60, 90, 'Recommended storage: 0 C, 95-100% relative humidity, 60-90 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(9, 'Lemon', 10.00, 13.00, 85.00, 90.00, 30, 180, 'Recommended storage: 10-13 C, 85-90% relative humidity, 30-180 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(10, 'Lime', 9.00, 10.00, 85.00, 90.00, 42, 56, 'Recommended storage: 9-10 C, 85-90% relative humidity, 42-56 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(11, 'Mango', 13.00, 13.00, 90.00, 95.00, 14, 21, 'Recommended storage: 13 C, 90-95% relative humidity, 14-21 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(12, 'Melon', 7.00, 10.00, 90.00, 95.00, 12, 21, 'Recommended storage: 7-10 C, 90-95% relative humidity, 12-21 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(13, 'Okra', 7.00, 10.00, 90.00, 95.00, 7, 10, 'Recommended storage: 7-10 C, 90-95% relative humidity, 7-10 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(14, 'Onions (dry)', 20.00, 25.00, 65.00, 70.00, 30, 240, 'Recommended storage: 20-25 C, 65-70% relative humidity, 30-240 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(15, 'Orange', 0.00, 9.00, 85.00, 90.00, 56, 84, 'Recommended storage: 0-9 C, 85-90% relative humidity, 56-84 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(16, 'Papaya', 7.00, 13.00, 85.00, 90.00, 7, 21, 'Recommended storage: 7-13 C, 85-90% relative humidity, 7-21 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(17, 'Cucumber', 5.00, 10.00, 95.00, 95.00, 28, 28, 'Recommended storage: 5-10 C, 95% relative humidity, 28 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(18, 'Bell pepper', 7.00, 13.00, 90.00, 95.00, 14, 21, 'Recommended storage: 7-13 C, 90-95% relative humidity, 14-21 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(19, 'Pineapple', 7.00, 13.00, 85.00, 90.00, 14, 28, 'Recommended storage: 7-13 C, 85-90% relative humidity, 14-28 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(20, 'Potato (early)', 7.00, 13.00, 90.00, 95.00, 10, 14, 'Recommended storage: 7-13 C, 90-95% relative humidity, 10-14 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(21, 'Radish', 0.00, 0.00, 95.00, 95.00, 21, 21, 'Recommended storage: 0 C, 95% relative humidity, 21 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(22, 'Spinach', 0.00, 0.00, 95.00, 100.00, 10, 14, 'Recommended storage: 0 C, 95-100% relative humidity, 10-14 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(23, 'Sweet potato', 13.00, 15.00, 85.00, 90.00, 120, 210, 'Recommended storage: 13-15 C, 85-90% relative humidity, 120-210 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(24, 'Water melon', 10.00, 15.00, 90.00, 90.00, 14, 21, 'Recommended storage: 10-15 C, 90% relative humidity, 14-21 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1),
(25, 'Yam', 16.00, 16.00, 70.00, 80.00, 60, 210, 'Recommended storage: 16 C, 70-80% relative humidity, 60-210 days.', 'Rajapaksha et al. (2021), Table 1; DOI: 10.15406/mojfpt.2021.09.00255', 1);

INSERT INTO products (product_id, farmer_id, category_id, product_name, description, unit_price, available_quantity, unit_label, listing_type, growing_method, shelf_life_reference_id, harvest_date, available_from_date, season_start_date, season_end_date, best_before_date, storage_guidance, listing_status) VALUES
(1, 3, 1, 'Nuwara Eliya Crisp Carrots', 'Farm-fresh carrots harvested from Nuwara Eliya.', 420.0, 150.0, 'kg', 'AVAILABLE_NOW', 'CONVENTIONAL', NULL, '2026-09-24', NULL, NULL, NULL, NULL, NULL, 'ACTIVE'),
(2, 3, 1, 'Dambulla Red Onions', 'Dry red onions supplied in kilogram quantities.', 380.0, 200.0, 'kg', 'AVAILABLE_NOW', 'CONVENTIONAL', 14, '2026-09-22', NULL, NULL, NULL, NULL, NULL, 'ACTIVE'),
(3, 3, 2, 'Jaffna Karutha Colomban Mangoes', 'Seasonal Karutha Colomban mangoes.', 650.0, 80.0, 'kg', 'SEASONAL', 'CONVENTIONAL', 11, NULL, NULL, '2026-05-01', '2026-08-31', NULL, NULL, 'ACTIVE'),
(4, 3, 3, 'Highland Gotu Kola Bundle', 'Fresh Gotu Kola sold in bundles.', 160.0, 50.0, 'bundle', 'AVAILABLE_NOW', 'MIXED', NULL, '2026-09-24', NULL, NULL, NULL, NULL, NULL, 'ACTIVE'),
(5, 8, 1, 'Dambulla Baby Potatoes', 'Washed baby potatoes from Dambulla farms.', 290.0, 125.0, 'kg', 'AVAILABLE_NOW', 'CONVENTIONAL', 20, '2026-09-23', NULL, NULL, NULL, NULL, NULL, 'ACTIVE'),
(6, 8, 2, 'Kandy Golden Pineapple', 'Naturally sweet pineapple selected at harvest.', 480.0, 60.0, 'piece', 'SEASONAL', 'CONVENTIONAL', 19, NULL, NULL, '2026-07-01', '2026-12-31', NULL, NULL, 'ACTIVE'),
(7, 8, 3, 'Ceylon Spinach Bunch', 'Fresh spinach picked in bunches.', 180.0, 90.0, 'bunch', 'AVAILABLE_NOW', 'MIXED', 22, '2026-09-24', NULL, NULL, NULL, NULL, NULL, 'ACTIVE'),
(8, 3, 4, 'Ceylon Cinnamon Quills', 'Locally produced Ceylon cinnamon quills.', 1250.0, 40.0, 'kg', 'AVAILABLE_NOW', 'CONVENTIONAL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACTIVE'),
(9, 8, 2, 'Matale Sweet Oranges', 'Harvest-soon oranges from the Matale growing area.', 360.0, 100.0, 'kg', 'HARVEST_SOON', 'CONVENTIONAL', 15, '2026-10-02', '2026-10-03', NULL, NULL, NULL, NULL, 'ACTIVE'),
(10, 8, 2, 'Sri Lankan Avocado', 'Seasonal avocado listing.', 520.0, 70.0, 'kg', 'SEASONAL', 'MIXED', 1, NULL, NULL, '2026-06-01', '2026-11-30', NULL, NULL, 'ACTIVE');

INSERT INTO product_images (image_id, product_id, image_path, is_primary) VALUES
(1, 1, 'assets/images/new.jpg', 1),
(2, 2, 'assets/images/Dambulla Red Onions.jpg', 1),
(3, 3, 'assets/images/Jaffna Karutha Colomban.jpg', 1),
(4, 4, 'assets/images/gotukola.jpg', 1),
(5, 5, 'assets/images/Dambulla Baby Potatoes.jpg', 1),
(6, 6, 'assets/images/Golden Pineapple.jpg', 1),
(7, 7, 'assets/images/Ceylon Spinach Bunch.jpg', 1),
(8, 8, 'assets/images/Ceylon Cinnamon Quills.jpg', 1),
(9, 9, 'assets/images/orange.jpg', 1),
(10, 10, 'assets/images/avacado.jpg', 1);

INSERT INTO carts (cart_id, buyer_id) VALUES
(1, 2),
(2, 7);

INSERT INTO cart_items (cart_item_id, cart_id, product_id, quantity) VALUES
(1, 1, 5, 2.0),
(2, 1, 6, 1.0),
(3, 2, 2, 1.0),
(4, 2, 3, 1.0);

INSERT INTO orders (order_id, buyer_id, farmer_id, source_type, recipient_name, recipient_phone, delivery_address_line1, delivery_address_line2, delivery_city_town, delivery_postal_code, destination_district_id, product_subtotal, farmer_marketplace_fee, buyer_service_fee, delivery_fee, grand_total, order_status, paid_at, accepted_at, ready_for_delivery_at, delivered_at, completed_at, cancelled_at, cancellation_reason) VALUES
(1, 2, 3, 'NORMAL', 'Kasun Jayasinghe', '+94 77 888 9999', '12 Rajagiriya Road', NULL, 'Colombo 08', '00800', 1, 1260.0, 25.2, 25.2, 440.0, 1725.2, 'COMPLETED', '2026-09-18 09:00:00', '2026-09-18 10:00:00', '2026-09-18 14:00:00', '2026-09-19 16:00:00', '2026-09-19 16:10:00', NULL, NULL),
(2, 2, 3, 'NORMAL', 'Kasun Jayasinghe', '+94 77 888 9999', '12 Rajagiriya Road', NULL, 'Colombo 08', '00800', 1, 570.0, 11.4, 11.4, 440.0, 1021.4, 'IN_TRANSIT', '2026-09-22 08:30:00', '2026-09-22 09:00:00', '2026-09-22 11:00:00', NULL, NULL, NULL, NULL),
(3, 7, 8, 'NORMAL', 'Tharushi Weerasinghe', '+94 71 234 1188', '45 Lake View Avenue', NULL, 'Kandy', '20000', 4, 480.0, 9.6, 9.6, 150.0, 639.6, 'READY_FOR_DELIVERY', '2026-09-24 08:15:00', '2026-09-24 08:45:00', '2026-09-24 12:00:00', NULL, NULL, NULL, NULL),
(4, 7, 3, 'NORMAL', 'Tharushi Weerasinghe', '+94 71 234 1188', '45 Lake View Avenue', NULL, 'Kandy', '20000', 4, 1250.0, 25.0, 25.0, 250.0, 1525.0, 'DELIVERED', '2026-09-23 07:45:00', '2026-09-23 08:15:00', '2026-09-23 10:30:00', '2026-09-24 16:10:00', NULL, NULL, NULL),
(5, 2, 8, 'NORMAL', 'Kasun Jayasinghe', '+94 77 888 9999', '12 Rajagiriya Road', NULL, 'Colombo 08', '00800', 1, 580.0, 11.6, 11.6, 380.0, 971.6, 'PENDING_PAYMENT', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 7, 8, 'NORMAL', 'Tharushi Weerasinghe', '+94 71 234 1188', '45 Lake View Avenue', NULL, 'Kandy', '20000', 4, 360.0, 7.2, 7.2, 150.0, 517.2, 'ACCEPTED', '2026-09-24 09:00:00', '2026-09-24 09:30:00', NULL, NULL, NULL, NULL, NULL);

INSERT INTO order_items (order_item_id, order_id, product_id, product_name_snapshot, unit_price_snapshot, quantity, line_total) VALUES
(1, 1, 1, 'Nuwara Eliya Crisp Carrots', 420.0, 3.0, 1260.0),
(2, 2, 2, 'Dambulla Red Onions', 380.0, 1.5, 570.0),
(3, 3, 6, 'Kandy Golden Pineapple', 480.0, 1.0, 480.0),
(4, 4, 8, 'Ceylon Cinnamon Quills', 1250.0, 1.0, 1250.0),
(5, 5, 5, 'Dambulla Baby Potatoes', 290.0, 2.0, 580.0),
(6, 6, 7, 'Ceylon Spinach Bunch', 180.0, 2.0, 360.0);

INSERT INTO order_status_history (history_id, order_id, status, changed_by_user_id, note, created_at) VALUES
(1, 1, 'PAID', 2, 'Local demo payment recorded.', '2026-09-18 09:00:00'),
(2, 1, 'ACCEPTED', 3, 'Farmer accepted the order.', '2026-09-18 10:00:00'),
(3, 1, 'READY_FOR_DELIVERY', 3, 'Order ready for courier pickup.', '2026-09-18 14:00:00'),
(4, 1, 'ASSIGNED', 5, 'Courier Partner assigned.', '2026-09-18 14:10:00'),
(5, 1, 'PICKED_UP', 5, 'Order collected from Farmer.', '2026-09-18 15:00:00'),
(6, 1, 'IN_TRANSIT', 5, 'Order is in transit.', '2026-09-18 16:00:00'),
(7, 1, 'OUT_FOR_DELIVERY', 5, 'Order is out for delivery.', '2026-09-19 09:00:00'),
(8, 1, 'DELIVERED', 5, 'Courier marked the order delivered.', '2026-09-19 16:00:00'),
(9, 1, 'COMPLETED', 2, 'Buyer confirmed receipt.', '2026-09-19 16:10:00'),
(10, 2, 'PAID', 2, 'Local demo payment recorded.', '2026-09-22 08:30:00'),
(11, 2, 'ACCEPTED', 3, 'Farmer accepted the order.', '2026-09-22 09:00:00'),
(12, 2, 'READY_FOR_DELIVERY', 3, 'Order ready for pickup.', '2026-09-22 11:00:00'),
(13, 2, 'ASSIGNED', 5, 'Courier Partner assigned.', '2026-09-22 11:10:00'),
(14, 2, 'PICKED_UP', 5, 'Order collected.', '2026-09-22 12:00:00'),
(15, 2, 'IN_TRANSIT', 5, 'Order in transit.', '2026-09-22 13:00:00'),
(16, 3, 'PAID', 7, 'Local demo payment recorded.', '2026-09-24 08:15:00'),
(17, 3, 'ACCEPTED', 8, 'Farmer accepted the order.', '2026-09-24 08:45:00'),
(18, 3, 'PREPARING', 8, 'Farmer preparing order.', '2026-09-24 10:00:00'),
(19, 3, 'READY_FOR_DELIVERY', 8, 'Order is ready for courier assignment.', '2026-09-24 12:00:00'),
(20, 4, 'DELIVERED', 5, 'Courier marked the order delivered; awaiting Buyer confirmation.', '2026-09-24 16:10:00');

INSERT INTO preorders (preorder_id, buyer_id, product_id, quantity, preorder_status, converted_order_id) VALUES
(1, 7, 9, 2.0, 'WAITING_HARVEST', NULL);

INSERT INTO payments (payment_id, order_id, provider, provider_reference, amount, payment_status, paid_at) VALUES
(1, 1, 'LOCAL_DEMO', 'DEMO-PAY-001', 1725.2, 'SUCCESS', '2026-09-18 09:00:00'),
(2, 2, 'LOCAL_DEMO', 'DEMO-PAY-002', 1021.4, 'SUCCESS', '2026-09-22 08:30:00'),
(3, 3, 'LOCAL_DEMO', 'DEMO-PAY-003', 639.6, 'SUCCESS', '2026-09-24 08:15:00'),
(4, 4, 'LOCAL_DEMO', 'DEMO-PAY-004', 1525.0, 'SUCCESS', '2026-09-23 07:45:00'),
(5, 5, 'LOCAL_DEMO', NULL, 971.6, 'PENDING', NULL),
(6, 6, 'LOCAL_DEMO', 'DEMO-PAY-006', 517.2, 'SUCCESS', '2026-09-24 09:00:00');

INSERT INTO earnings (earning_id, order_id, beneficiary_type, beneficiary_user_id, earning_type, amount, earning_status, settled_at) VALUES
(1, 1, 'FARMER', 3, 'FARMER_NET', 1234.8, 'PAID', '2026-09-21 10:00:00'),
(2, 1, 'COURIER_PARTNER', 5, 'COURIER_DELIVERY', 440.0, 'PAID', '2026-09-21 10:00:00'),
(3, 1, 'PLATFORM', NULL, 'FARMER_MARKETPLACE_FEE', 25.2, 'EARNED', NULL),
(4, 1, 'PLATFORM', NULL, 'BUYER_SERVICE_FEE', 25.2, 'EARNED', NULL),
(5, 2, 'FARMER', 3, 'FARMER_NET', 558.6, 'PENDING_PAYOUT', NULL),
(6, 2, 'COURIER_PARTNER', 5, 'COURIER_DELIVERY', 440.0, 'HELD', NULL),
(7, 3, 'FARMER', 8, 'FARMER_NET', 470.4, 'PENDING_PAYOUT', NULL),
(8, 4, 'FARMER', 3, 'FARMER_NET', 1225.0, 'PENDING_PAYOUT', NULL),
(9, 4, 'COURIER_PARTNER', 5, 'COURIER_DELIVERY', 250.0, 'HELD', NULL),
(10, 6, 'FARMER', 8, 'FARMER_NET', 352.8, 'PENDING_PAYOUT', NULL);

INSERT INTO settlements (settlement_id, period_start, period_end, total_amount, settlement_status, processed_by, processed_at) VALUES
(1, '2026-09-15', '2026-09-21', 1674.8, 'COMPLETED', 1, '2026-09-21 10:00:00');

INSERT INTO settlement_items (settlement_item_id, settlement_id, earning_id) VALUES
(1, 1, 1),
(2, 1, 2);

INSERT INTO delivery_assignment_offers (offer_id, order_id, courier_partner_id, offer_status, rejection_reason, offered_at, expires_at, responded_at) VALUES
(1, 1, 5, 'ACCEPTED', NULL, '2026-09-18 14:05:00', '2026-09-18 14:35:00', '2026-09-18 14:08:00'),
(2, 2, 5, 'ACCEPTED', NULL, '2026-09-22 11:05:00', '2026-09-22 11:35:00', '2026-09-22 11:08:00'),
(3, 3, 5, 'PENDING', NULL, '2026-09-24 12:05:00', '2026-09-24 12:35:00', NULL),
(4, 4, 5, 'ACCEPTED', NULL, '2026-09-23 10:35:00', '2026-09-23 11:05:00', '2026-09-23 10:40:00');

INSERT INTO deliveries (delivery_id, order_id, courier_partner_id, delivery_status, assigned_at, accepted_at, picked_up_at, in_transit_at, out_for_delivery_at, delivered_at, buyer_confirmation_deadline, completed_at) VALUES
(1, 1, 5, 'COMPLETED', '2026-09-18 14:08:00', '2026-09-18 14:08:00', '2026-09-18 15:00:00', '2026-09-18 16:00:00', '2026-09-19 09:00:00', '2026-09-19 16:00:00', '2026-09-21 16:00:00', '2026-09-19 16:10:00'),
(2, 2, 5, 'IN_TRANSIT', '2026-09-22 11:08:00', '2026-09-22 11:08:00', '2026-09-22 12:00:00', '2026-09-22 13:00:00', NULL, NULL, NULL, NULL),
(3, 3, NULL, 'PENDING_ASSIGNMENT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 4, 5, 'DELIVERED', '2026-09-23 10:40:00', '2026-09-23 10:40:00', '2026-09-23 11:30:00', '2026-09-23 12:00:00', '2026-09-24 09:30:00', '2026-09-24 16:10:00', '2026-09-26 16:10:00', NULL);

INSERT INTO delivery_attempts (attempt_id, delivery_id, attempt_number, attempt_result, notes, attempted_at) VALUES
(1, 1, 1, 'DELIVERED', 'Buyer received the order.', '2026-09-19 16:00:00'),
(2, 4, 1, 'BUYER_UNAVAILABLE', 'Buyer was unavailable on the first delivery attempt.', '2026-09-24 11:00:00'),
(3, 4, 2, 'DELIVERED', 'Order delivered successfully on the second attempt.', '2026-09-24 16:10:00');

INSERT INTO reviews (review_id, order_id, buyer_id, farmer_id, rating, review_text, farmer_response, farmer_responded_at) VALUES
(1, 1, 2, 3, 5, 'Fresh produce and good service.', 'Thank you for your feedback.', '2026-09-20 10:00:00');

INSERT INTO complaints (complaint_id, order_id, complainant_user_id, complainant_role, category, description, evidence_path, complaint_status, admin_response, reviewed_by, reviewed_at, resolved_at) VALUES
(1, 2, 2, 'BUYER', 'Late Delivery', 'The delivery is taking longer than expected.', NULL, 'UNDER_REVIEW', NULL, 1, '2026-09-24 09:00:00', NULL),
(2, 4, 3, 'FARMER', 'Delivery Handling', 'The Farmer reported a handling concern during delivery.', NULL, 'RESOLVED', 'Issue reviewed and handling guidance was provided to the Courier Partner.', 1, '2026-09-25 09:00:00', '2026-09-25 09:00:00');

INSERT INTO notifications (notification_id, user_id, notification_type, title, message, related_order_id, is_read, read_at) VALUES
(1, 1, 'VERIFICATION_PENDING', 'Farmer approval pending', 'Rohan Fernando is waiting for Admin verification.', NULL, 0, NULL),
(2, 1, 'VERIFICATION_PENDING', 'Courier Partner approval pending', 'Ceylon Fresh Transit is waiting for Admin verification.', NULL, 0, NULL),
(3, 2, 'ORDER_UPDATE', 'Order in transit', 'Your order ORD-2026-2 is currently in transit.', 2, 0, NULL),
(4, 3, 'ORDER_UPDATE', 'Order completed', 'Order ORD-2026-1 has been completed.', 1, 1, '2026-09-20 08:00:00'),
(5, 5, 'DELIVERY_ASSIGNMENT', 'New delivery assignment', 'A delivery assignment is available for ORD-2026-3.', 3, 0, NULL),
(6, 7, 'ORDER_UPDATE', 'Delivery awaiting confirmation', 'Order ORD-2026-4 was marked Delivered. Please confirm receipt.', 4, 0, NULL);

COMMIT;
