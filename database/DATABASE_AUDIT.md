# Harvestly database audit (final revised scope)

## Kept tables and why

- `districts`, `district_distances`: 25-district delivery and approximate district-reference delivery-fee calculation.
- `users`, role profile tables, `verification_documents`, `password_reset_tokens`: common authentication, approvals, profiles, forgot/reset password.
- `courier_coverage_routes`: Courier Partner origin-district -> destination-district coverage.
- `product_categories`, `products`, `product_images`: Farmer product CRUD and Buyer browsing.
- `shelf_life_references`: optional reference storage data. Products link to it only when a matching reference exists.
- `carts`, `cart_items`: general Buyer cart.
- `orders`, `order_items`, `order_status_history`, `preorders`: checkout, order lifecycle, Harvest Soon/pre-order support.
- `payments`: local demo payment records now; PayHere Sandbox can replace the provider after approval.
- `earnings`, `settlements`, `settlement_items`: Farmer/Courier earnings and weekly payout-record workflow; no real bank payout.
- `delivery_assignment_offers`, `deliveries`, `delivery_attempts`: route-based assignment, retry after rejection/no response, delivery status, max two attempts, Confirm Received/48h workflow.
- `reviews`, `complaints`, `notifications`: review, common issue workflow, database notifications.
- `platform_settings`: Admin-configurable fees/timeouts.

## Removed fields/features

- All quality-grade tables/columns: removed because the quality-grade feature is out of the revised system.
- `shelf_life_references.default_shelf_life_days`: removed because reference maximum storage life must not be converted automatically into a best-before date.
- `products.freshness_tracking_applicable`: removed because `shelf_life_reference_id IS NULL/NOT NULL` already tells whether reference shelf-life information exists.
- `orders.review_deadline`: removed because the 14-day review window can be calculated from `completed_at` + the Admin setting.
- `deliveries.active_attempt_count`: removed because delivery attempts are already stored in `delivery_attempts` and can be counted.
- Workload/priority ranking fields: not present. Assignment is route + approval + availability + retry, with Admin fallback.

## Shelf-life rule

`products.shelf_life_reference_id` is nullable. A product receives an ID only when the supplied reference table has a suitable matching crop. `best_before_date` and `storage_guidance` stay `NULL` for every seeded product, because a reference storage life must never be converted into a best-before date. For the sample data:

| Product | Reference ID | Reference crop | Reference storage life (days) |
| --- | --- | --- | --- |
| Dambulla Red Onions | 14 | Onions (dry) | 30-240 |
| Jaffna Karutha Colomban Mangoes | 11 | Mango | 14-21 |
| Dambulla Baby Potatoes | 20 | Potato (early) | 10-14 |
| Kandy Golden Pineapple | 19 | Pineapple | 14-28 |
| Ceylon Spinach Bunch | 22 | Spinach | 10-14 |
| Matale Sweet Oranges | 15 | Orange | 56-84 |
| Sri Lankan Avocado | 1 | Avocado | 14-56 |

No suitable row in the supplied reference table, so `shelf_life_reference_id` is `NULL`:

- Nuwara Eliya Crisp Carrots
- Highland Gotu Kola Bundle
- Ceylon Cinnamon Quills

The Buyer UI LEFT JOINs `shelf_life_references` and renders the reference section only when `shelf_life_reference_id` is not NULL. Farmer product forms only offer references whose `is_active = 1`.

## Important seed corrections

- Categories are now seeded only once with stable IDs; the earlier schema/seed category-ID conflict is removed.
- Pending Farmers have no active product listings.
- Pending Courier Partners are `UNAVAILABLE` and are not seeded into active coverage routes.
- All order subtotals, fees, grand totals, order items and payment amounts are internally consistent.
- `delivery_assignment_offers` no longer seeds the removed `priority_rank` column.
- Payment sample rows use `LOCAL_DEMO`, not PayHere, because PayHere Sandbox is still pending approval.
- The complete installer drops/recreates the `harvestly` database, preventing stale foreign-key data from causing the shelf-life error.

## Final schema state

30 tables in one `harvestly` database. No column remains for quality grading, GPS/lat-long, live location, delivery zones, OTP confirmation, disputes/arbitration, vehicles/fleet/hubs, workload ranking, chat, or a hard-coded 90/10 split. Order pricing is driven entirely by `platform_settings` (`farmer_marketplace_fee_percent`, `buyer_service_fee_percent`, `delivery_base_fee`, `delivery_per_km_rate`, `buyer_confirmation_hours`, `review_window_days`, `max_delivery_attempts`), so no fee is a fixed number in code.

`orders.order_status` enum (in order): `PENDING_PAYMENT`, `PAID`, `ACCEPTED`, `PREPARING`, `READY_FOR_DELIVERY`, `PENDING_ASSIGNMENT`, `ASSIGNED`, `PICKED_UP`, `IN_TRANSIT`, `OUT_FOR_DELIVERY`, `DELIVERED`, `COMPLETED`, `UNDELIVERABLE`, `REJECTED`, `CANCELLED`.

`products.listing_status` enum is only `ACTIVE`, `INACTIVE`, `EXPIRED`, `SOLD_OUT`, so the Admin Flag/Remove actions map onto those values. `complaints` uses `admin_response` for the Admin outcome. `payments.provider` is `LOCAL_DEMO`. `order_status_history.changed_by_user_id` is nullable so system and scheduled actions can be recorded without inventing a user.

## Verification performed against this schema

- `scripts/integration-test.php` — 78 model-level assertions across the full lifecycle (registration, approval, password reset, one full CRUD per actor, checkout, assignment, delivery, confirmation, review, complaint). Creates and removes its own fixtures only.
- `scripts/smoke_test.php` — 169 HTTP assertions over the real pages, including the out-of-scope sweep, cross-role access control, CSRF rejection and a PHP-diagnostic check.
- `scripts/page_diagnostics.php` — 46 role pages, 0 with problems.
- `scripts/check_prepared_statements.php` — 262 prepared-statement call sites across 112 files, all placeholders/types/values in parity.
- `scripts/check_css_classes.php` — every CSS class used in every view is defined by the stylesheets that view actually loads.
- PHP lint over 122 files and `node --check` over 5 scripts: 0 errors.
