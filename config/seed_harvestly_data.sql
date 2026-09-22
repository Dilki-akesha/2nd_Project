-- Seed default users, profiles, categories, products, orders, payments, settlements, complaints into harvestly database
USE harvestly;

-- 1. Insert Default Admin & Users
INSERT INTO users (user_id, role, full_name, email, password_hash, phone, account_status) VALUES
(1, 'ADMIN', 'System Administrator', 'admin@harvestly.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 11 234 5678', 'ACTIVE'),
(2, 'BUYER', 'Kasun Jayasinghe', 'buyer@gmail.com', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 77 888 9999', 'ACTIVE'),
(3, 'FARMER', 'Sunil Perera', 'farmer@harvestly.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 77 123 4567', 'ACTIVE'),
(4, 'FARMER', 'Rohan Fernando', 'rohan@farm.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 76 345 6789', 'PENDING'),
(5, 'COURIER_PARTNER', 'Lanka Agro Logistics', 'courier@lankaagro.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 11 234 9999', 'ACTIVE'),
(6, 'COURIER_PARTNER', 'Ceylon Fresh Transit', 'info@ceylontransit.lk', '$2y$10$EJJb8beLrsmAgHIf.8.6ROkqPyKD92zu7iEXx0/OATeSinmwiffPK', '+94 81 445 6789', 'PENDING')
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), account_status = VALUES(account_status);

-- 2. Buyer Profile
INSERT INTO buyer_profiles (buyer_id, default_address_line1, default_city_town, default_district_id) VALUES
(2, '12 Rajagiriya Road', 'Colombo 08', 1)
ON DUPLICATE KEY UPDATE default_address_line1 = VALUES(default_address_line1);

-- 3. Farmer Profiles
INSERT INTO farmer_profiles (farmer_id, farm_name, pickup_address_line1, district_id, verification_status) VALUES
(3, 'Sunlight Highlands Farm', 'Moon Plains, Nuwara Eliya', 6, 'APPROVED'),
(4, 'Vadamarachchi Orchards', 'Point Pedro Road, Jaffna', 10, 'PENDING')
ON DUPLICATE KEY UPDATE verification_status = VALUES(verification_status);

-- 4. Courier Profiles
INSERT INTO courier_partner_profiles (courier_partner_id, organisation_name, contact_person_name, office_district_id, verification_status) VALUES
(5, 'Lanka Agro Logistics Pvt Ltd', 'Nimal Silva', 1, 'APPROVED'),
(6, 'Ceylon Fresh Transit Co.', 'Dinesh Wijesinghe', 6, 'PENDING')
ON DUPLICATE KEY UPDATE verification_status = VALUES(verification_status);

-- 5. Verification Documents
INSERT INTO verification_documents (user_id, document_type, original_file_name, stored_file_path, status) VALUES
(3, 'National Identity Card (NIC)', 'nic_sunil.jpg', 'assets/docs/nic_sunil.jpg', 'APPROVED'),
(4, 'National Identity Card (NIC)', 'nic_rohan.jpg', 'assets/docs/nic_rohan.jpg', 'PENDING'),
(5, 'Business Verification Document', 'doc_lanka_agro.pdf', 'assets/docs/doc_lanka_agro.pdf', 'APPROVED'),
(6, 'Business Verification Document', 'doc_ceylon_fresh.pdf', 'assets/docs/doc_ceylon_fresh.pdf', 'PENDING')
ON DUPLICATE KEY UPDATE status = VALUES(status);

-- 6. Product Categories
INSERT IGNORE INTO product_categories (category_id, category_name, is_active) VALUES
(1, 'Vegetables', 1),
(2, 'Fresh Fruits', 1),
(3, 'Leafy Greens', 1),
(4, 'Spices & Staples', 1),
(5, 'Organic Produce', 1);

-- 7. Products
INSERT INTO products (product_id, farmer_id, category_id, product_name, description, unit_price, available_quantity, unit_label, listing_type, declared_grade, listing_status) VALUES
(1, 3, 1, 'Nuwara Eliya Crisp Carrots', 'Farm-fresh organic carrots harvested daily', 420.00, 150.000, 'kg', 'AVAILABLE_NOW', 'A', 'ACTIVE'),
(2, 3, 1, 'Dambulla Red Onions', 'High quality local red onions', 380.00, 200.000, 'kg', 'AVAILABLE_NOW', 'A', 'ACTIVE'),
(3, 3, 2, 'Jaffna Karutha Colomban Mangoes', 'Sweet seasonal tropical mangoes', 650.00, 80.000, 'kg', 'SEASONAL', 'A', 'ACTIVE'),
(4, 3, 3, 'Highland Gotu Kola Bundle', 'Fresh nutrient-rich leafy green bundles', 160.00, 50.000, 'bundle', 'AVAILABLE_NOW', 'A', 'ACTIVE')
ON DUPLICATE KEY UPDATE product_name = VALUES(product_name);

-- 8. Orders
INSERT INTO orders (order_id, buyer_id, farmer_id, recipient_name, recipient_phone, delivery_address_line1, destination_district_id, product_subtotal, delivery_fee, grand_total, order_status) VALUES
(1, 2, 3, 'Kasun Jayasinghe', '+94 77 888 9999', '12 Rajagiriya Road, Colombo 08', 1, 1260.00, 150.00, 1410.00, 'DELIVERED'),
(2, 2, 3, 'Kasun Jayasinghe', '+94 77 888 9999', '12 Rajagiriya Road, Colombo 08', 1, 670.00, 220.00, 890.00, 'IN_TRANSIT'),
(3, 2, 3, 'Kasun Jayasinghe', '+94 77 888 9999', '12 Rajagiriya Road, Colombo 08', 1, 650.00, 150.00, 800.00, 'PENDING_ASSIGNMENT')
ON DUPLICATE KEY UPDATE order_status = VALUES(order_status);

-- 9. Order Items
INSERT INTO order_items (order_item_id, order_id, product_id, product_name_snapshot, unit_price_snapshot, quantity, line_total, declared_grade_snapshot) VALUES
(1, 1, 1, 'Nuwara Eliya Crisp Carrots', 420.00, 3.000, 1260.00, 'A'),
(2, 2, 2, 'Dambulla Red Onions', 380.00, 1.760, 670.00, 'A'),
(3, 3, 3, 'Jaffna Karutha Colomban Mangoes', 650.00, 1.000, 650.00, 'A')
ON DUPLICATE KEY UPDATE product_name_snapshot = VALUES(product_name_snapshot);

-- 10. Deliveries
INSERT INTO deliveries (delivery_id, order_id, courier_partner_id, delivery_status) VALUES
(1, 1, 5, 'DELIVERED'),
(2, 2, 5, 'IN_TRANSIT'),
(3, 3, NULL, 'PENDING_ASSIGNMENT')
ON DUPLICATE KEY UPDATE delivery_status = VALUES(delivery_status);

-- 11. Payments
INSERT INTO payments (payment_id, order_id, provider, provider_reference, amount, payment_status, paid_at) VALUES
(1, 1, 'PAYHERE_SANDBOX', 'PAYHERE-REF-001', 1410.00, 'SUCCESS', '2026-09-01 10:00:00'),
(2, 2, 'PAYHERE_SANDBOX', 'PAYHERE-REF-002', 890.00, 'SUCCESS', '2026-09-03 09:15:00'),
(3, 3, 'PAYHERE_SANDBOX', 'PAYHERE-REF-003', 800.00, 'PENDING', NULL)
ON DUPLICATE KEY UPDATE payment_status = VALUES(payment_status);

-- 12. Settlements
INSERT INTO settlements (settlement_id, period_start, period_end, total_amount, settlement_status) VALUES
(1, '2026-09-01', '2026-09-07', 1260.00, 'COMPLETED')
ON DUPLICATE KEY UPDATE total_amount = VALUES(total_amount);

-- 13. Complaints
INSERT INTO complaints (complaint_id, order_id, complainant_user_id, complainant_role, category, description, complaint_status) VALUES
(1, 1, 2, 'BUYER', 'Damaged Goods', 'Produce cartons were squished during transit resulting in damaged carrots.', 'OPEN')
ON DUPLICATE KEY UPDATE complaint_status = VALUES(complaint_status);

-- 14. Notifications
INSERT INTO notifications (notification_id, user_id, notification_type, title, message, is_read) VALUES
(1, 1, 'VERIFICATION_PENDING', 'New Farmer Pending Verification', 'Farmer Rohan Fernando submitted registration documents for Admin review.', 0)
ON DUPLICATE KEY UPDATE title = VALUES(title);
