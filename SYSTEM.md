# 🏪 Online Shoes Selling System

A full-stack e-commerce platform for buying and selling shoes online, built as a college project.

**Tech Stack:** HTML · CSS · JavaScript (Frontend) | PHP (Backend) | MySQL (Database) | n8n (Automation)

---

## 📌 What Is This System?

An online shoe store where customers can browse, search, and buy shoes. Admins manage products, orders, and inventory. Managers handle stock. The system tracks everything from registration to delivery.

---

## 🛒 Services It Provides

| Service | Description |
|---|---|
| **Product Catalog** | Browse shoes by category (Running, Casual, Trail, Performance), search by name/brand, view details with sizes & colors |
| **Shopping Cart** | Add/remove items, update quantities. Server-side for logged-in users, localStorage for guests |
| **Checkout & Orders** | Place orders with shipping address and payment method (COD, Card, UPI). Auto-generates order numbers |
| **Order Tracking** | Customers track order status (Pending → Processing → Shipped → Delivered). Admin can add tracking numbers |
| **User Accounts** | Register, login, update profile, reset password. Separate customer and admin login |
| **Wishlist** | Save favorite products for later |
| **Coupons** | Apply discount codes at checkout (e.g., WELCOME10 for 10% off) |
| **Reviews & Ratings** | Customers rate products (1-5 stars) and leave comments |
| **Admin Dashboard** | View total revenue, today's revenue, order counts, customer counts, low-stock alerts |
| **Inventory Management** | Track stock levels per size/color, get low-stock alerts, update quantities, view stock history |
| **User Management** | Admin can list, search, update roles, and delete users |
| **Category Management** | Admin can create, update, and delete product categories |
| **Automation (n8n)** | Webhook hooks for order placed, order shipped, low stock alerts — connects to n8n workflows |

---

## 👥 How It Works — By User Role

### 🧑 Customer
1. **Browse** → Visit homepage, see trending products, filter by category, or search
2. **View Details** → Click a product to see sizes, colors, stock availability, and reviews
3. **Add to Wishlist** → Save products for later
4. **Add to Cart** → Select size/color and add to cart
5. **Register/Login** → Create account or login to proceed to checkout
6. **Checkout** → Enter shipping address, choose payment (COD/Card/UPI), apply coupon code
7. **Track Order** → View order history and status in account dashboard
8. **Leave Review** → Rate and review delivered products

### 🔑 Admin (admin@shoestore.com / password)
1. **Dashboard** → See KPIs: revenue, orders, customers, low-stock count
2. **Manage Orders** → View all orders, update status (pending → processing → shipped → delivered), add tracking numbers
3. **Manage Products** → Create, edit, delete products. Set prices and discounts
4. **Manage Categories** → Create, edit, delete shoe categories
5. **Manage Users** → View all users, change roles, delete accounts
6. **Manage Coupons** → Create discount codes, set expiry dates, deactivate coupons
7. **View Inventory** → See all stock levels, identify low-stock items

### 📦 Manager (manager@shoestore.com / password)
1. **Inventory Dashboard** → View stock levels across all product variants (size/color/SKU)
2. **Low Stock Alerts** → Filter to see only items below threshold
3. **Update Stock** → Adjust quantities when new shipments arrive
4. **Add Variants** → Add new size/color combinations to existing products
5. **Stock History** → All stock changes are logged for audit trail

---

## 🗄️ Database Structure

| Table | Purpose |
|---|---|
| `users` | All accounts (customer, admin, manager) with bcrypt-hashed passwords |
| `categories` | Product categories (Running, Casual, Trail, Performance) |
| `products` | Shoe catalog with brand, price, discount price, image |
| `product_variants` | Size/color/SKU combinations with individual stock levels |
| `orders` | Customer purchases with status, address, payment, tracking number |
| `order_items` | Line items linking orders to specific variants |
| `cart_items` | Server-side cart for logged-in users |
| `reviews` | Product ratings (1-5) and comments |
| `wishlist` | Saved products per user |
| `coupons` | Discount codes with percentage, usage limits, expiry |
| `stock_history` | Audit log of every stock change (orders, manual updates) |
| `password_resets` | Secure token-based password reset |

---

## 🔌 API Endpoints

| Endpoint | Methods | Auth | Purpose |
|---|---|---|---|
| `/api/auth.php` | GET, POST | Public/User | Register, login, logout, profile, password reset |
| `/api/products.php` | GET, POST, PUT, DELETE | Public / Admin | Product catalog CRUD with search, filter, pagination |
| `/api/categories.php` | GET, POST, PUT, DELETE | Public / Admin | Category management |
| `/api/cart.php` | GET, POST, PUT, DELETE | Customer | Server-side cart operations |
| `/api/orders.php` | GET, POST, PUT | Customer / Admin | Order placement, listing, status updates |
| `/api/inventory.php` | GET, POST, PUT, DELETE | Admin/Manager | Stock management with history logging |
| `/api/reviews.php` | GET, POST | Public / Customer | Product reviews and ratings |
| `/api/wishlist.php` | GET, POST, DELETE | Customer | Wishlist management |
| `/api/coupons.php` | GET, POST, PUT, DELETE | Public / Admin | Coupon validation and management |
| `/api/users.php` | GET, PUT, DELETE | Admin | User management |
| `/api/stats.php` | GET | Admin/Manager | Dashboard statistics |

---

## 🔒 Security Features

- **Password Hashing** — bcrypt via `password_hash()`
- **SQL Injection Prevention** — PDO prepared statements everywhere
- **Session Security** — httpOnly, SameSite=Lax, secure cookies, session regeneration
- **CORS** — Whitelist of allowed origins (no wildcard with credentials)
- **Role-Based Access** — `requireRole()` enforces admin/manager/customer permissions
- **Security Headers** — X-Content-Type-Options, X-Frame-Options, Referrer-Policy
- **Token-Based Password Reset** — 1-hour expiry, single-use tokens

---

## 🚀 How to Run

1. **Start XAMPP** → Run Apache and MySQL
2. **Import Database** → Open phpMyAdmin, run `backend/sql/schema.sql` then `backend/sql/schema_update.sql`
3. **Open in Browser** → Go to `http://localhost/college/frontend/index.html`

### Demo Accounts
| Role | Email | Password |
|---|---|---|
| Admin | admin@shoestore.com | password |
| Manager | manager@shoestore.com | password |
| Customer | demo@shoestore.com | password |

### Sample Coupons
| Code | Discount |
|---|---|
| WELCOME10 | 10% off |
| SUMMER20 | 20% off |
| FLAT15 | 15% off |

---

## 📁 Project Structure

```
college/
├── frontend/
│   ├── index.html          # Homepage
│   ├── products.html       # Product listing
│   ├── product-detail.html # Single product
│   ├── cart.html            # Shopping cart
│   ├── checkout.html        # Checkout
│   ├── login.html           # Admin/Manager login
│   ├── customer-login.html  # Customer login
│   ├── account.html         # Customer dashboard
│   ├── admin.html           # Admin dashboard
│   ├── inventory.html       # Inventory management
│   ├── about.html           # About page
│   ├── contact.html         # Contact page
│   ├── styles.css           # Global styles (glassmorphism)
│   ├── script.js            # Main JS logic
│   ├── api-config.js        # API helper & config
│   ├── auth.js              # Auth utilities
│   └── img/                 # Product images
│
├── backend/
│   ├── config/
│   │   └── database.php     # MySQL PDO connection
│   ├── api/
│   │   ├── config.php       # Shared API config, CORS, helpers
│   │   ├── auth.php         # Authentication + password reset
│   │   ├── products.php     # Product CRUD + pagination
│   │   ├── categories.php   # Category CRUD
│   │   ├── cart.php         # Cart operations
│   │   ├── orders.php       # Orders + stock deduction
│   │   ├── inventory.php    # Stock management + history
│   │   ├── reviews.php      # Reviews & ratings
│   │   ├── wishlist.php     # Wishlist
│   │   ├── coupons.php      # Coupon management
│   │   ├── users.php        # Admin user management
│   │   ├── stats.php        # Dashboard statistics
│   │   └── webhooks.php     # n8n webhook helper
│   └── sql/
│       ├── schema.sql       # Main database + seed data
│       └── schema_update.sql # Additional tables (wishlist, coupons, etc.)
│
├── plan/                    # Planning documents
├── SYSTEM.md                # ← This file
└── README.md
```
