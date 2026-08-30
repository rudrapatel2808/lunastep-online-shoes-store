# 📋 Required Changes — Online Shoes Selling System

## Project Info

| Property | Details |
|---|---|
| **Project Title** | Online Shoes Selling System |
| **Type** | Web-based E-commerce Platform |
| **Tech Stack** | HTML5, CSS3, JS · PHP · MySQL · n8n |
| **Group No.** | 20 |
| **Guide** | Ms. Abha Damani |
| **Institute** | Babu Madhav Institute of Information Technology |

---

## Change 1 — Add Project Title to Slide 1

**File:** PPT — Slide 1

**Current:**
```
Project Title       ← generic placeholder
```

**Change to:**
```
Online Shoes Selling System
```

---

## Change 2 — Remove Square Brackets from Student Names (Slide 1)

**File:** PPT — Slide 1

**Current:**
```
1. [Mahuvagara Joy Hiteshbhai] [202407100110032]
2. [Patel Rudra Fulchandbhai] [202407100110023]
3. [Patel Prince Prakashbhai] [20247100110074]
4. [Bhavsar Smit PiyushKumar] [202407100110057]
```

**Change to:**
```
1. Mahuvagara Joy Hiteshbhai — 202407100110032
2. Patel Rudra Fulchandbhai — 202407100110023
3. Patel Prince Prakashbhai — 20247100110074
4. Bhavsar Smit PiyushKumar — 202407100110057
```

---

## Change 3 — Project Definition in Paragraph (Slide 3)

**File:** PPT — Slide 3

**Current (bullet points):**
```
• Online Shoe Selling System is a web-based e-commerce application.
• Simplifies buying and selling footwear online.
• Supports browsing, cart, wishlist, checkout, orders and tracking.
```

**Change to (single paragraph):**
```
The Online Shoe Selling System is a comprehensive web-based e-commerce
application designed to simplify the buying and selling of footwear
through the internet. It provides a seamless shopping experience for
customers by enabling them to browse products, search and filter shoes
by brand, size, color and category, add items to cart and wishlist,
securely checkout with multiple payment options, place orders, and
track deliveries in real-time. The platform also offers robust
management tools for administrators to manage products, inventory,
orders, customers, and promotions efficiently.
```

---

## Change 4 — Separate Purpose and Scope (Slide 4)

**File:** PPT — Slide 4

**Current (combined):**
```
Purpose & Scope
• Provide secure and user-friendly online shopping.
• Automate product browsing to delivery.
• Manage products, inventory, orders, users and promotions.
```

**Change to (two separate sections):**

### Purpose
```
The purpose of this project is to develop a secure, user-friendly
and automated online shoe selling platform that enables customers
to browse, search, and purchase footwear conveniently from anywhere.
The system aims to eliminate the limitations of traditional shoe
shopping by providing a digital storefront with real-time inventory
management, secure payment processing, automated order notifications,
and a seamless checkout experience.
```

### Scope
```
The scope of this project covers the development of a full-stack
web-based e-commerce application with the following modules:

• Customer Module — Registration, login, product browsing,
  search/filter, cart, wishlist, checkout, order tracking,
  reviews & ratings.

• Admin Module — Product management, category & brand management,
  order processing, return handling, coupon/discount management,
  customer management, sales reports & analytics dashboard.

• Inventory Module — Stock management (add/update/delete),
  low-stock alerts, inventory reports, and stock history audit trail.

• Payment Module — Cash on Delivery (COD), UPI payments,
  and Credit/Debit card processing.

• Notification Module — Automated email notifications via n8n
  for order confirmations, shipping updates, and promotional campaigns.
```

---

## Change 5 — Functional Requirements as Statements (Slide 6)

**File:** PPT — Slide 6

**Current (short phrases):**
```
User Registration - Allows new users to create an account.
User Login - Authenticates users securely.
Product Management - Enables admins to manage products.
Product Search & Browsing - Lets users search and filter shoes.
Shopping Cart & Wishlist - Add, remove, and update products.
Checkout & Payment - Complete purchases securely.
Order Tracking - Track order status.
Coupons & Discounts - Apply promotional offers.
Notifications - Receive order updates.
```

**Change to (formal statements):**
```
1. The system shall allow new users to register an account by
   providing their name, email, phone number, and password.

2. The system shall authenticate registered users securely
   using email and password with encrypted session management.

3. The system shall enable administrators to add, edit, and
   delete products including their name, description, price,
   brand, category, images, sizes, and color variants.

4. The system shall allow customers to search products by
   name, brand, or keyword, and filter results by category,
   size, color, and price range.

5. The system shall allow registered customers to add products
   to their shopping cart and wishlist, update quantities, and
   remove items as needed.

6. The system shall provide a secure multi-step checkout process
   supporting Cash on Delivery (COD), UPI, and Credit/Debit
   card payment methods.

7. The system shall enable customers to track their order status
   in real-time with updates including Confirmed, Packed, Shipped,
   Out for Delivery, and Delivered.

8. The system shall allow administrators to create, manage, and
   apply coupon codes and discount offers with configurable
   parameters such as percentage, minimum order value, and expiry.

9. The system shall send automated email notifications to
   customers for order confirmation, shipment updates, delivery
   confirmation, and promotional announcements via n8n workflow
   automation.
```

---

## Change 6 & 9 — Change Data Dictionary (Slide 10)

**File:** PPT — Slide 10

**Current (incomplete — only 3 tables, few fields):**
```
| Table    | Field        | Data Type    | Description       |
|----------|--------------|--------------|-------------------|
| Users    | user_id      | INT          | Primary Key       |
| Users    | name         | VARCHAR(100) | Customer Name     |
| Users    | email        | VARCHAR(100) | Login Email       |
| Products | product_id   | INT          | Product ID        |
| Products | name         | VARCHAR(100) | Shoe Name         |
| Products | price        | DECIMAL      | Shoe Price        |
| Orders   | order_id     | INT          | Order ID          |
| Orders   | total_amount | DECIMAL      | Total Price       |
| Cart     | quantity     | INT          | Product Quantity  |
```

**Change to (complete 8-table data dictionary):**

### Table: users

| Field | Data Type | Constraint | Description |
|---|---|---|---|
| id | INT | PK, AUTO_INCREMENT | Unique user identifier |
| first_name | VARCHAR(50) | NOT NULL | User's first name |
| last_name | VARCHAR(50) | NOT NULL | User's last name |
| email | VARCHAR(100) | UNIQUE, NOT NULL | Login email address |
| password_hash | VARCHAR(255) | NOT NULL | Bcrypt hashed password |
| phone | VARCHAR(20) | — | Contact phone number |
| role | ENUM('customer','admin','manager') | DEFAULT 'customer' | User role for access control |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Account creation date |

### Table: categories

| Field | Data Type | Constraint | Description |
|---|---|---|---|
| id | INT | PK, AUTO_INCREMENT | Unique category identifier |
| name | VARCHAR(50) | NOT NULL | Category name (Running, Casual, etc.) |
| description | TEXT | — | Category description |

### Table: products

| Field | Data Type | Constraint | Description |
|---|---|---|---|
| id | INT | PK, AUTO_INCREMENT | Unique product identifier |
| category_id | INT | FK → categories(id) | Associated category |
| brand | VARCHAR(50) | — | Brand name (Nike, Adidas, etc.) |
| name | VARCHAR(100) | NOT NULL | Product display name |
| description | TEXT | — | Detailed product description |
| base_price | DECIMAL(10,2) | NOT NULL | Original selling price |
| discount_price | DECIMAL(10,2) | NULLABLE | Discounted price if applicable |
| image_url | VARCHAR(255) | — | Product image path |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Product creation date |

### Table: product_variants

| Field | Data Type | Constraint | Description |
|---|---|---|---|
| id | INT | PK, AUTO_INCREMENT | Unique variant identifier |
| product_id | INT | FK → products(id) | Parent product reference |
| size | VARCHAR(10) | NOT NULL | Shoe size (8, 9, 10, etc.) |
| color | VARCHAR(20) | NOT NULL | Shoe color (Black, Red, etc.) |
| sku | VARCHAR(50) | UNIQUE, NOT NULL | Stock Keeping Unit code |
| stock_quantity | INT | DEFAULT 0 | Current stock count |
| low_stock_threshold | INT | DEFAULT 5 | Alert threshold for low stock |

### Table: orders

| Field | Data Type | Constraint | Description |
|---|---|---|---|
| id | INT | PK, AUTO_INCREMENT | Unique order identifier |
| user_id | INT | FK → users(id) | Customer who placed the order |
| order_number | VARCHAR(50) | UNIQUE, NOT NULL | Human-readable order number |
| total_amount | DECIMAL(10,2) | NOT NULL | Total order value |
| status | ENUM('pending','processing','shipped','delivered','cancelled') | DEFAULT 'pending' | Current order status |
| shipping_address | TEXT | NOT NULL | Delivery address |
| payment_method | VARCHAR(50) | NOT NULL | Payment method (cod/card/upi) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Order placement date |

### Table: order_items

| Field | Data Type | Constraint | Description |
|---|---|---|---|
| id | INT | PK, AUTO_INCREMENT | Unique order item identifier |
| order_id | INT | FK → orders(id) | Parent order reference |
| variant_id | INT | FK → product_variants(id) | Purchased product variant |
| quantity | INT | NOT NULL | Quantity ordered |
| price_at_purchase | DECIMAL(10,2) | NOT NULL | Price at the time of purchase |

### Table: reviews

| Field | Data Type | Constraint | Description |
|---|---|---|---|
| id | INT | PK, AUTO_INCREMENT | Unique review identifier |
| product_id | INT | FK → products(id) | Reviewed product |
| user_id | INT | FK → users(id) | Review author |
| rating | INT | CHECK(1–5), NOT NULL | Star rating (1 to 5) |
| comment | TEXT | — | Review text |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Review submission date |

### Table: cart_items

| Field | Data Type | Constraint | Description |
|---|---|---|---|
| id | INT | PK, AUTO_INCREMENT | Unique cart item identifier |
| user_id | INT | FK → users(id), NOT NULL | Cart owner |
| variant_id | INT | FK → product_variants(id), NOT NULL | Selected product variant |
| quantity | INT | DEFAULT 1 | Quantity in cart |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date added to cart |

---

## Change 7 — Remove Conclusion Slide

**File:** PPT — Slide 12

**Current:**
```
Conclusion
• Provides complete online shoe shopping solution.
• Improves customer experience and store management.
• Secure, scalable and efficient e-commerce platform.
```

**Action:** Delete Slide 12 entirely.

---

## Change 8 — Add Use Case & Activity Diagrams (Slides 8 & 9)

**File:** PPT — Slides 8 & 9

**Current:** Both slides are empty (title only, no diagram).

### Use Case Diagram (Slide 8)

**Actors:**
- Customer (left side)
- Admin (right side)
- Inventory Manager (right side, below Admin)

**Customer Use Cases:**
- Register / Login
- Browse & Search Products
- Filter by Brand / Size / Color / Price
- View Product Details
- Add to Cart / Wishlist
- Checkout & Pay (COD, UPI, Card)
- Track Order
- Write Review & Rating
- Request Return

**Admin Use Cases:**
- Login to Admin Panel
- Manage Products (Add/Edit/Delete)
- Manage Categories & Brands
- Manage Orders (Update Status)
- Manage Returns & Refunds
- Manage Coupons & Discounts
- View Sales Analytics & Reports
- Manage Users

**Inventory Manager Use Cases:**
- Login
- Add / Update / Delete Stock
- View Low Stock Alerts
- Generate Inventory Reports
- View Stock History

### Activity Diagram — Customer Order Flow (Slide 9)

```
[Start]
   ↓
Browse / Search Products
   ↓
View Product Details
   ↓
Select Size & Color
   ↓
Add to Cart
   ↓
Continue Shopping? ──Yes──→ (back to Browse)
   ↓ No
View Cart
   ↓
Logged In? ──No──→ Login / Register ──→ (back to View Cart)
   ↓ Yes
Proceed to Checkout
   ↓
Enter Shipping Address
   ↓
Select Payment Method
   ├── COD ──→ Place COD Order ──────────────→ Order Confirmed
   ├── UPI ──→ Process UPI Payment ──→ Success? ─Yes→ Order Confirmed
   └── Card ─→ Process Card Payment ─→ Success? ─Yes→ Order Confirmed
                                         ↓ No
                                    (back to Select Payment)
   ↓
Order Confirmed
   ↓
Send Confirmation Email (n8n)
   ↓
Customer Tracks Order
   ↓
Order Delivered
   ↓
Write Review & Rating
   ↓
[End]
```

> **Note:** Create these as proper UML diagrams using draw.io, Lucidchart,
> StarUML, or any diagram tool. Then paste the images into the PPT slides.

---

## Summary

| # | Change | Slide | Action |
|---|---|---|---|
| 1 | Add project title | Slide 1 | Replace "Project Title" → "Online Shoes Selling System" |
| 2 | Remove `[ ]` from names | Slide 1 | Remove all square brackets |
| 3 | Project Definition paragraph | Slide 3 | Convert bullets → paragraph |
| 4 | Separate Purpose & Scope | Slide 4 | Split into two sections |
| 5 | Functional requirements statements | Slide 6 | Rewrite as "The system shall…" |
| 6 & 9 | Change data dictionary | Slide 10 | Replace with complete 8-table schema |
| 7 | Remove conclusion | Slide 12 | Delete the slide |
| 8 | Add diagrams | Slides 8 & 9 | Add Use Case & Activity diagrams |
