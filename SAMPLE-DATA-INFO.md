# 📦 Sample Data Information

## ✅ Sample Data Successfully Added!

Your database has been populated with realistic factory equipment data.

## 📊 What Was Added:

### 8 Categories:
1. ✅ Electric Forklifts
2. ✅ Gas Forklifts
3. ✅ Pallet Trucks
4. ✅ Reach Trucks
5. ✅ Stackers
6. ✅ Order Pickers
7. ✅ Sideloaders
8. ✅ Attachments

### 13 Products:

#### Electric Forklifts:
1. **Yale ERP25VT** - 2.5-ton capacity, $26,500 (on sale)
2. **Toyota 8FGU25** - 5,000 lb capacity, $42,500
3. **Crown SC 4500** - 4,500 lb capacity, $34,500 (on sale)

#### Gas Forklifts:
4. **CAT EP16C2** - 16,000 lb capacity, $68,500
5. **Hyster H50XM** - 10,000 lb capacity, $49,500 (on sale)

#### Pallet Trucks:
6. **Raymond Easi** - Manual, $750 (on sale)
7. **Crown WP 3000** - Electric walkie, $4,200

#### Reach Trucks:
8. **Toyota 8FBRT25** - Narrow aisle, $45,500 (on sale)

#### Stackers:
9. **Yale MSW025** - Electric walkie stacker, $12,500

#### Order Pickers:
10. **Raymond 102XM** - High-level picker, $38,500

#### Sideloaders:
11. **Kalmar DRF 450-1200** - Heavy-duty, $185,000

#### Attachments:
12. **Rotating Clamp** - $11,000 (on sale)
13. **Fork Positioner** - $4,800

## 📝 Product Details Include:

- ✅ Product names and SKUs
- ✅ Detailed descriptions
- ✅ Specifications (capacity, dimensions, etc.)
- ✅ Features lists
- ✅ Pricing (regular and sale prices)
- ✅ Stock status
- ✅ Categories
- ✅ Featured products

## 🎯 View Your Products:

1. **Homepage**: Visit `http://localhost:8080` to see featured products
2. **All Products**: Visit `http://localhost:8080/products.php`
3. **By Category**: Click on any category to filter products
4. **Product Details**: Click on any product to see full details

## 💡 Next Steps:

### To Add More Data:

1. **Run the script again** (it will skip existing items):
   ```bash
   php database/sample-data.php
   ```

2. **Add via Admin Panel**:
   - Login: `http://localhost:8080/admin/login.php`
   - Go to Products → Add New Product
   - Fill in all details

3. **Import additional tables** (for FAQs, Testimonials, Blog):
   ```bash
   mysql -u root -p forklift_equipment < database/even-more-features.sql
   ```
   Then run the sample data script again to populate those tables.

## 📸 Adding Images:

To add product images:
1. Upload images to `storage/uploads/` folder
2. Edit products in admin panel
3. Select uploaded images

**Recommended image sizes:**
- Main product image: 800x600px
- Gallery images: 1024x768px
- Thumbnails: 300x300px

## 🎉 Your Website is Ready!

Your website now has:
- ✅ 8 product categories
- ✅ 13 realistic factory equipment products
- ✅ Complete product information
- ✅ Pricing and specifications
- ✅ Ready for customers!

Visit `http://localhost:8080` to see your populated website! 🚀

