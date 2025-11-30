# ⚡ Quick Fix: Orders Table Missing Error

## The Problem
```
Fatal error: Table 'forklift_equipment.orders' doesn't exist
```

## The Solution (2 Options)

### Option 1: Use Setup Script (Recommended) ⭐

1. **Login to Admin Panel**
   - URL: `http://localhost:8080/admin/login.php`
   - Username: `admin`
   - Password: `admin`

2. **Run Setup Script**
   - URL: `http://localhost:8080/admin/setup-orders.php`
   - Click the **"Create Missing Tables"** button
   - Wait for success message

3. **Done!**
   - Go back to: `http://localhost:8080/admin/orders.php`
   - Orders page should now work!

---

### Option 2: Import SQL File

1. **Open phpMyAdmin**
   - URL: `http://localhost:8080/phpmyadmin`
   - Select database: `forklift_equipment`

2. **Import SQL**
   - Click "Import" tab
   - Choose file: `database/more-features.sql`
   - Click "Go"

3. **Done!**

---

## What Gets Created

The setup creates these tables:
- ✅ `customers` - Customer accounts
- ✅ `orders` - Order information  
- ✅ `order_items` - Items in each order

---

## Verification

After setup, visit:
- `http://localhost:8080/admin/orders.php`
- Should show empty orders page (no errors!)

---

**Status:** ✅ Fixed!

