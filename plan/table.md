# Database Tables (MySQL)

The relational database for the **Online Shoes Selling System** is designed to efficiently store and retrieve e-commerce data. Below are the primary tables and their core attributes.

## 1. `users` Table
Stores account information for all system users (customers, admins, staff).
- `id` (INT, Primary Key, Auto Increment)
- `first_name` (VARCHAR)
- `last_name` (VARCHAR)
- `email` (VARCHAR, Unique)
- `password_hash` (VARCHAR)
- `phone` (VARCHAR)
- `role` (ENUM: 'customer', 'admin', 'inventory')
- `created_at` (TIMESTAMP)

## 2. `categories` Table
Organizes products into distinct groups.
- `id` (INT, Primary Key)
- `name` (VARCHAR) (e.g., Running, Casual, Boots)
- `description` (TEXT)

## 3. `products` Table
Holds core details of the shoe catalog.
- `id` (INT, Primary Key)
- `category_id` (INT, Foreign Key)
- `brand` (VARCHAR)
- `name` (VARCHAR)
- `description` (TEXT)
- `base_price` (DECIMAL)
- `discount_price` (DECIMAL, Nullable)
- `created_at` (TIMESTAMP)

## 4. `product_variants` (Inventory) Table
Handles sizes, colors, and specific stock levels for products.
- `id` (INT, Primary Key)
- `product_id` (INT, Foreign Key)
- `size` (VARCHAR)
- `color` (VARCHAR)
- `sku` (VARCHAR, Unique)
- `stock_quantity` (INT)
- `low_stock_threshold` (INT)

## 5. `orders` Table
Tracks customer purchases.
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key)
- `total_amount` (DECIMAL)
- `status` (ENUM: 'pending', 'processing', 'shipped', 'delivered', 'cancelled')
- `shipping_address` (TEXT)
- `payment_method` (VARCHAR)
- `created_at` (TIMESTAMP)

## 6. `order_items` Table
Maps specific product variants to orders.
- `id` (INT, Primary Key)
- `order_id` (INT, Foreign Key)
- `variant_id` (INT, Foreign Key)
- `quantity` (INT)
- `price_at_purchase` (DECIMAL)

## 7. `reviews` Table
Stores customer feedback on products.
- `id` (INT, Primary Key)
- `product_id` (INT, Foreign Key)
- `user_id` (INT, Foreign Key)
- `rating` (INT, 1-5)
- `comment` (TEXT)
- `created_at` (TIMESTAMP)
