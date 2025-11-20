# Multi-Store Payment Form Setup Instructions

## Overview
This payment form system supports **multiple WooCommerce stores** using a single installation. Each store is identified by a unique `store_id` parameter.

---

## Step 1: Configure Store Credentials

Open the file `config.php` and update the `$WC_STORES` array with your store information:

```php
$WC_STORES = [
    'store1' => [
        'url' => 'https://your-first-store.com',
        'consumer_key' => 'ck_xxxxxxxxxxxxx',
        'consumer_secret' => 'cs_xxxxxxxxxxxxx',
    ],
    'store2' => [
        'url' => 'https://your-second-store.com',
        'consumer_key' => 'ck_yyyyyyyyyyyyy',
        'consumer_secret' => 'cs_yyyyyyyyyyyyy',
    ],
    // Add up to 6 stores (or more if needed)
];
```

### How to get WooCommerce API credentials:
1. Log in to your WooCommerce admin panel
2. Go to: **WooCommerce → Settings → Advanced → REST API**
3. Click **"Add key"**
4. Set permissions to **Read/Write**
5. Copy the **Consumer Key** and **Consumer Secret**

---

## Step 2: Update WooCommerce Plugin URLs

In your WooCommerce payment plugin, you need to add the `store_id` parameter to the checkout URL.

### Example URL format:
```
https://online-bill.click/checkout/RESOURCE/TOTAL/ORDER_ID/HASH?store_id=store1&first_name=John&last_name=Doe&email=john@example.com&...
```

### Required parameters:
- `store_id` - **REQUIRED** - Must match one of the keys in `$WC_STORES` array (e.g., "store1", "store2")
- All other customer data parameters (first_name, last_name, email, etc.)

---

## Step 3: Test Each Store

For each store, test the payment flow:

1. **Create a test order** in your WooCommerce store
2. **Proceed to checkout** - you should be redirected to `https://online-bill.click/checkout/...?store_id=storeX`
3. **Complete the payment form**
4. **Verify** that:
   - Order data is saved in the dashboard
   - Store ID is displayed correctly in the dashboard
   - WooCommerce order status changes to "Processing"

---

## Step 4: Monitor Logs

Check PHP error logs for any issues with WooCommerce API calls:

```bash
tail -f /var/log/php8.3-fpm.log
```

You should see log entries like:
```
WooCommerce order 12345 (store: store1) status updated to processing
```

If there are errors:
```
WooCommerce status update failed for store store1, order 12345: HTTP 401
```

This means your API credentials are incorrect.

---

## URL Format Reference

### Path-based format (from WooCommerce plugin):
```
/checkout/RESOURCE/TOTAL_CENTS/ORDER_ID/HASH?store_id=storeX&params...
```

### Query string parameters:
- `store_id` - Store identifier (REQUIRED)
- `id` - Order ID
- `total` - Total amount in cents
- `first_name` - Customer first name
- `last_name` - Customer last name
- `email` - Customer email
- `phone` - Customer phone
- `address` - Billing address
- `city` - City
- `state` - State/Province (name or code)
- `country` - Country (name or code)
- `zip` - ZIP/Postal code
- `success` - Success redirect URL
- `failure` - Failure redirect URL
- `back` - Back to shop URL

---

## Dashboard Features

The dashboard at `https://online-bill.click/dashboard` shows:

- **Store ID badge** - Shows which store the order came from
- **Processed checkbox** - Mark orders as processed
- **Search** - Search by email, name, or order ID
- **Export CSV** - Export all orders to CSV
- **View Details** - Expand to see full customer and payment data

### Login credentials:
- Username: `admin`
- Password: `admin123`

**⚠️ IMPORTANT:** Change these credentials in `config.php` before going live!

---

## Security Notes

1. **This system stores full card data in plain text** - This is NOT PCI compliant
2. **Use only for authorized security testing or educational purposes**
3. **SSL/TLS is required** - All traffic must be encrypted
4. **Protect the data directory** - The `.htaccess` file prevents direct access to `/data`
5. **Change default admin password** immediately

---

## Troubleshooting

### Issue: "Invalid store configuration" in logs
**Solution:** Check that `store_id` parameter matches a key in `$WC_STORES` array

### Issue: WooCommerce status not updating
**Solution:**
- Verify API credentials are correct
- Check that WooCommerce REST API is enabled
- Ensure order ID is valid in that store

### Issue: Country/State not auto-filling
**Solution:** The system now auto-converts country/state names to codes. If still not working, check the URL parameters being sent.

### Issue: Store ID shows "N/A" in dashboard
**Solution:** The `store_id` parameter was not included in the checkout URL. Update your WooCommerce plugin to include it.

---

## Support

For issues or questions:
- Check error logs: `/var/log/php8.3-fpm.log`
- Check Nginx logs: `/var/log/nginx/error.log`
- Review dashboard order details for missing data
