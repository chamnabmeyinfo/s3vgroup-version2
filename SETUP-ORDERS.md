# 🔧 Setup Orders Management

## Quick Fix for "Table 'orders' doesn't exist" Error

If you're getting this error:
```
Fatal error: Table 'forklift_equipment.orders' doesn't exist
```

### Solution: Run the Setup Script

1. **Login to Admin Panel**
   - Go to: `http://localhost:8080/admin/login.php`
   - Login with: `admin` / `admin`

2. **Run Setup Script**
   - Go to: `http://localhost:8080/admin/setup-orders.php`
   - Click **"Create Missing Tables"** button
   - Wait for setup to complete

3. **That's It!**
   - Tables will be created automatically
   - You can now use Orders Management

---

## What Gets Created

The setup script creates these tables:

1. ✅ **`customers`** - Customer account information
2. ✅ **`orders`** - Order information
3. ✅ **`order_items`** - Items in each order

---

## Alternative: Manual Setup

If you prefer to run SQL manually, you can import:

```bash
# Option 1: Import the SQL file
mysql -u root -p forklift_equipment < database/more-features.sql

# Option 2: Use phpMyAdmin
# - Go to phpMyAdmin
# - Select forklift_equipment database
# - Click Import
# - Choose database/more-features.sql
```

---

## Verification

After setup, you can verify by:

1. Going to: `http://localhost:8080/admin/orders.php`
2. You should see the orders page (even if empty)
3. No errors should appear

---

## Need Help?

If you still have issues:
1. Check that database connection is working
2. Verify you're using the correct database name
3. Check file permissions

**Status:** ✅ Ready to use after setup!

