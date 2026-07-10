# Complete All Missing Features — Online Shoes Selling System

## Goal
Fix all bugs and implement every missing feature from the IRD analysis. Keep code short and simple.

---

## Proposed Changes

### Database Schema Update

#### [NEW] [schema_update.sql](file:///d:/xampp/htdocs/college/backend/sql/schema_update.sql)
Add missing tables:
- `wishlist` (user_id, product_id)
- `coupons` (code, discount_percent, active, expiry)
- `stock_history` (variant_id, change_qty, reason, created_at)
- `password_resets` (email, token, expires_at)
- Add `tracking_number` column to `orders` table

---

### Bug Fixes

#### [MODIFY] [orders.php](file:///d:/xampp/htdocs/college/backend/api/orders.php)
- **Fix stock deduction** — decrement `stock_quantity` inside the order transaction
- **Fix guest spoofing** — only use session user_id, never trust `$data->user_id`
- **Add tracking_number** support in PUT (admin can add tracking info)

#### [MODIFY] [products.php](file:///d:/xampp/htdocs/college/backend/api/products.php)
- Add `LIMIT`/`OFFSET` pagination to GET

---

### New API Files (all kept short ~50-80 lines each)

#### [NEW] [wishlist.php](file:///d:/xampp/htdocs/college/backend/api/wishlist.php)
- GET: list user's wishlist
- POST: add product
- DELETE: remove product

#### [NEW] [coupons.php](file:///d:/xampp/htdocs/college/backend/api/coupons.php)
- GET: list/validate coupon
- POST: create coupon (admin)
- DELETE: deactivate coupon (admin)

#### [NEW] [users.php](file:///d:/xampp/htdocs/college/backend/api/users.php)
- GET: list all users / single user (admin)
- PUT: update user role (admin)
- DELETE: delete user (admin)

#### [NEW] [categories.php](file:///d:/xampp/htdocs/college/backend/api/categories.php)
- GET: list all categories
- POST: create (admin)
- PUT: update (admin)
- DELETE: delete (admin)

---

### Auth Updates

#### [MODIFY] [auth.php](file:///d:/xampp/htdocs/college/backend/api/auth.php)
- Add `password-reset-request` action (generates token, stores in DB)
- Add `password-reset` action (validates token, updates password)

---

### n8n Webhook Hooks

#### [NEW] [webhooks.php](file:///d:/xampp/htdocs/college/backend/api/webhooks.php)
- Simple helper: `triggerWebhook($event, $data)` — fire-and-forget POST to n8n URL
- Events: `order_placed`, `order_shipped`, `low_stock_alert`
- Called from `orders.php` after order creation/status update
- Webhook URL configurable in `database.php`

---

### Final Documentation

#### [NEW] [SYSTEM.md](file:///d:/xampp/htdocs/college/SYSTEM.md)
Complete system description: what it is, services it provides, how it works for each user role.

---

## Verification Plan
- Check all new PHP files parse without syntax errors (`php -l`)
- Verify SQL schema runs clean
