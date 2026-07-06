# Backend Architecture

The backend of the **Online Shoes Selling System** handles business logic, data persistence, and system automation securely and efficiently.

## Technology Stack
- **Server-Side Language:** PHP
- **Database:** MySQL
- **Web Server:** Apache (via XAMPP/WAMP)
- **Workflow Automation:** n8n

## Core Backend Responsibilities
1. **Authentication & Authorization:** Secure user registration, password hashing (bcrypt), login sessions, and role-based access control (Admin, Customer, Inventory).
2. **Product & Catalog Management:** APIs/endpoints to fetch product lists, filter results, and manage catalog data (CRUD operations for admins).
3. **Order Processing:** Securely handling cart data, calculating totals, applying discounts, and processing checkouts.
4. **Inventory Tracking:** Updating stock levels upon order confirmation and triggering low-stock events.

## Database Interaction (MySQL)
The PHP backend interacts with the MySQL database using secure methods (like PDO with prepared statements) to prevent SQL injection and ensure data integrity.

## Automation via n8n
**n8n** is integrated via webhooks to handle asynchronous, event-driven tasks without blocking the main PHP server threads:
- **Order Confirmations:** Sending formatted HTML emails upon successful checkout.
- **Abandoned Carts:** Triggering follow-up emails if a cart remains inactive for a set period.
- **Admin Alerts:** Sending daily sales summaries or low-stock warnings to store managers.
- **Invoice Generation:** Automating the creation and delivery of PDF invoices.
