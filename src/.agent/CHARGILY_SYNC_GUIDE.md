# Chargily Payment Status Synchronization

## Overview
This document explains how payment statuses are synchronized between your QwenShop system and Chargily Pay.

## Three Methods of Status Synchronization

### 1. **Webhook (Automatic - Recommended)**
**Location:** `src/webhook_chargily.php`

**How it works:**
- Chargily automatically sends payment status updates to your server.
- Happens in real-time when payment status changes (paid, failed, expired).
- Works even if the customer closes the browser or doesn't return to your site.

**Configuration:**
```
Webhook URL: https://yoursite.com/QwenShop - Bootstrap/src/webhook_chargily.php
Set this in your Chargily Dashboard
```

---

### 2. **Auto-Verification (Automatic on Page Load)**
**Locations:**
- `src/admin/admin_order_details.php`
- `src/admin/orders.php`
- `src/order_history.php`

**How it works:**
- When an order that is still "pending" with Chargily is viewed, the system automatically queries the Chargily API in the background.
- This ensures the status is updated even if the webhook was missed.

---

## How to Check Payment Status from Chargily API

The logic used in `src/admin/chargily_payments.php` is:

```php
// 1. Initialize Chargily Service
$chargily = new ChargilyService();

// 2. Get specific checkout by ID
$checkout = $chargily->getCheckout($transactionId);

// 3. Check status
if ($checkout) {
    $status = $checkout->getStatus(); // 'paid', 'pending', 'failed', 'expired'
    
    // 4. Get payment method details
    $paymentMethod = $checkout->getPaymentMethod(); // 'edahabia', 'cib', etc.
    
    // 5. Get full data
    $fullData = $checkout->toArray();
}
```

---

## Payment Status Flow

```
Customer Orders → Order Created (status: pending, payment: pending)
                     ↓
Customer Clicks "Pay Now" → Redirected to Chargily
                     ↓
        Chargily Checkout ID saved to transaction_id
                     ↓
        +------------------------+
        | Customer pays?         |
        +------------------------+
         ↙YES                 ↘ NO
    PAID                    FAILED/EXPIRED
     ↓                           ↓
Webhook fires              Webhook fires
     ↓                           ↓
status='paid'             status='failed'
order='processing'        order='cancelled'
     ↓                           ↓
Email sent                Email sent
```

---

## Troubleshooting

### Order stuck in "Pending" but paid on Chargily?

**Check:**
1. Is webhook configured correctly in Chargily dashboard?
2. Check `src/debug_payment.log` for webhook delivery logs
3. Manually click "Sync with Chargily" button
4. View order details page (triggers auto-verification)

### Webhook not working on localhost?

**Solution:**
- Use `src/simulate_webhook.php` for testing
- Or use Ngrok/Cloudflare Tunnel to expose localhost to internet

### How to verify webhook is working?

**Check `src/debug_payment.log`:**
```
2026-01-11 07:40:45 - Webhook Request Received. Method: POST
2026-01-11 07:40:45 - Webhook Signature Header: [signature_value]
2026-01-11 07:40:45 - Webhook Verified Event: checkout.paid
2026-01-11 07:40:45 - Order #80 PAID (Tx: 01kep00ccay1t3mtt7g8znefw5)
```

---

## Database Fields

**orders table:**
- `payment_method` - 'chargily', 'cod', etc.
- `payment_status` - 'pending', 'paid', 'failed', 'refunded'
- `transaction_id` - Chargily Checkout ID
- `payment_gateway_response` - JSON containing full Chargily response

**Example payment_gateway_response:**
```json
{
  "id": "01kep00ccay1t3mtt7g8znefw5",
  "status": "paid",
  "payment_method": "edahabia",
  "amount": 15000,
  "customer_id": "...",
  "metadata": {
    "order_id": "80"
  }
}
```

---

## Summary

| Method | Timing | Location | Works on Localhost? |
|--------|--------|----------|---------------------|
| Webhook | Real-time (seconds) | Chargily → Your Server | ❌ No |
| Auto-Verification | On page view | Admin/Customer views order | ✅ Yes |
| Manual Sync | On button click | Admin Order Details | ✅ Yes |

**Best Practice:**
- Use Webhooks for production (most reliable)
- Use Auto-Verification + Manual Sync for development and backup
