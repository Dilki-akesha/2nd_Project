# Harvestly Interim-Ready Scope-Clean Build

This build is aligned to the revised Farmer-to-Buyer Marketplace scope.

## Included
Common authentication; Buyer, Farmer, Courier Partner and Admin roles; all 25 Sri Lankan districts; district reference distances; Courier Partner district-to-district coverage routes; Farmer product/listing and inventory management; general Buyer cart and checkout; one-Farmer-per-order validation; order processing; automatic Courier Partner offer/retry; two delivery attempts; Buyer Confirm Received; 48-hour scheduled automatic completion; 14-day review window; one common complaint/issue workflow; database notifications; configurable Farmer marketplace and Buyer service fees; delivery-fee calculation; earnings and recorded weekly settlements; Admin approvals, monitoring, settings, reports and Product Category CRUD.

## Intentionally excluded
Quality-grade/A-B-C grading, GPS/maps/geocoding, live tracking, zones/hubs/multi-leg routing, individual drivers/vehicles/fleet management, OTP delivery confirmation, chat, AI grading/certification, a separate dispute module, social login/OAuth, and real bank payout/refund processing.

The actors are exactly four: **Buyer**, **Farmer**, **Courier Partner** and **Admin**. There is no Seller, Home Gardener, Driver or Delivery Person anywhere in the schema, the code or the UI.

## Technology
HTML, CSS, vanilla JavaScript, core PHP and MySQL. All database access is MySQLi with prepared statements. There are no frameworks, libraries, Composer/npm packages, CDNs or external font/icon/map/push services — `JS/Common/local-icons.js` provides every glyph locally, so the project renders fully offline.

## Payment status
PayHere Sandbox is the planned provider, subject to approval. The project does not fake a PayHere transaction and does not collect or store card details. Until approval/integration is available, newly created checkout orders stay `PENDING_PAYMENT` and `payments.provider` is `LOCAL_DEMO`. The seed data contains demonstration orders for showing later order/delivery workflows.

## Database setup
One database: `harvestly`. Connection settings are in `config/database.php`.

Fresh setup — a single import is enough:

```
mysql -u root < database/harvestly_final_clean_install.sql
```

`harvestly_final_clean_install.sql` drops and recreates the database, creates all 30 tables and inserts the settings, the 25 districts, the 625 district distance rows and the full demonstration dataset.

Supporting files:

| File | Purpose |
| --- | --- |
| `database/harvestly_final_clean_install.sql` | Complete schema + demonstration data. The recommended install. |
| `database/harvestly_final_schema.sql` | Schema only, no sample rows. |
| `database/harvestly_final_seed.sql` | Demonstration data only, for use with the schema-only file. |

Do not run the schema and the seed against the clean installer — the installer already inserts the same rows.

See `database/DATABASE_AUDIT.md` for the table-by-table audit, the shelf-life rule and the verification results.

## Demo accounts
All demonstration accounts use the password `TestPass123!`:

| Role | Email |
| --- | --- |
| Admin | `admin@harvestly.lk` |
| Buyer | `buyer@gmail.com` |
| Farmer | `farmer@harvestly.lk` |
| Courier Partner | `courier@lankaagro.lk` |

The password is stored only as a bcrypt hash in the SQL. `scripts/reset_demo_passwords.php` re-applies the same password to the live database and clears leftover smoke-test accounts.

## Scheduled tasks
Use Windows Task Scheduler with the PHP executable from XAMPP for these scripts:
- `scripts/retry_courier_offers.php` — expires unanswered Courier Partner offers and attempts the next eligible partner.
- `scripts/auto_complete_deliveries.php` — completes Delivered orders after the configured Buyer confirmation window (default 48 hours).
- `scripts/weekly_settlements.php` — records eligible pending payouts as settled/paid inside Harvestly. It does not perform a real bank transfer.

## Verification scripts
Run these from the project root to confirm a change did not break anything:

| Script | What it does |
| --- | --- |
| `php scripts/smoke_test.php` | 169 HTTP assertions over the real pages: every actor's login, dashboard, one full CRUD per actor, the out-of-scope sweep, cross-role access control, CSRF rejection and a PHP-diagnostic check. |
| `php scripts/integration-test.php` | 78 model-level assertions driving the whole order lifecycle directly against the database. Creates and deletes only its own fixtures. |
| `php scripts/page_diagnostics.php` | Requests all 46 role pages as a logged-in actor and reports any page with problems. |
| `php scripts/check_prepared_statements.php` | Static check that every prepared statement has matching placeholders, type string and value count. |
| `php scripts/check_css_classes.php` | Static check that every CSS class used in a view is defined by the stylesheets that view loads. Catches layouts that silently collapse. |
| `php scripts/reset_demo_passwords.php` | Resets the demo accounts to `TestPass123!`. |
| `php scripts/issue_password_reset.php <email>` | Support utility: issues a single-use password reset link for a verified account owner. |

`bindMysqliParams()` in `config/app.php` throws on any type/value mismatch, so these scripts fail loudly on a broken write instead of silently skipping it.

`scripts/` is command-line only — `scripts/.htaccess` denies web access, and every script refuses to run outside the CLI SAPI.

## Folder naming note
The Courier Partner views live in `View/Delivey/`. The misspelling is deliberately kept so the existing include paths keep working; the actor is always labelled **Courier Partner** in the UI, the code and the documentation.

## Opening the site
This build lives at:

```
C:\xampp\htdocs\Harvestly-final\2nd_Project
```

so the correct URL is:

```
http://localhost/Harvestly-final/2nd_Project/index.php
```

> `C:\xampp\htdocs` also contains older Harvestly copies (`Harvestly`, `Harvestly-merged`, `Harvestly_Interim_Ready_Scope_Clean\Harvestly`, `Harvestly_Final_Integrated`, `Harvestly_No_External_Libraries`, `Harvestly_No_External_Libraries_FarmerFix`). They are **not** this repository. Do not open those URLs.

MySQL must be running (XAMPP → Start → MySQL) before the site will load.

The test scripts default to this URL. To point them elsewhere, set `HARVESTLY_BASE`, for example:

```
set HARVESTLY_BASE=http://localhost/Harvestly-final/2nd_Project
php scripts/smoke_test.php
```
