# System Workflows

The **Online Shoes Selling System** operates through several key workflows depending on the user role.

## 1. Customer Shopping Flow
1. **Discovery:** Customer lands on the homepage, views trending items, or searches/filters for specific shoes (by brand, size, category).
2. **Selection:** Customer views product details, selects a size and color, and clicks "Add to Cart".
3. **Authentication:** If not logged in, the customer is prompted to log in or register before proceeding to checkout.
4. **Checkout:** Customer reviews cart, enters shipping address, selects payment method (COD, Card, UPI), and places the order.
5. **Confirmation:** System displays an order confirmation page. The backend (via n8n) sends a confirmation email.
6. **Post-Purchase:** Customer can track the order status in their profile and leave a review once delivered.

## 2. Order Fulfillment Flow (Admin/Staff)
1. **Order Received:** Admin views new orders in the dashboard ('Pending' status).
2. **Processing:** Stock is verified. The order is marked as 'Processing' while being packed.
3. **Dispatch:** The order is handed to delivery, status updated to 'Shipped', and tracking info is added. (n8n triggers shipment email to customer).
4. **Delivery:** Once confirmed by the delivery partner, status is updated to 'Delivered'.

## 3. Inventory Management Flow
1. **Monitoring:** Inventory staff views the dashboard for low-stock alerts.
2. **Restock:** Staff receives new shipment from suppliers.
3. **Update:** Staff adds stock to the specific `product_variant` (SKU).
4. **Logging:** System records the stock update in the history logs for auditing.

## 4. AI Recommendation Flow (Conceptual)
1. **Data Collection:** System logs user views, cart additions, and past purchases.
2. **Analysis:** (Simulated in this version) The system matches current user behavior against historical data.
3. **Display:** "Frequently Bought Together" or "Recommended for You" sections are dynamically populated on the product and landing pages based on these insights.
