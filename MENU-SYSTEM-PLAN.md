# WordPress-Style Menu System - Implementation Plan

## Overview

Upgrade the current hardcoded navigation menu to a dynamic, WordPress-like menu management system with drag-and-drop ordering, hierarchical menus, and menu location assignments.

## Current State Analysis

- **Current Menu**: Hardcoded in `includes/header.php` (lines 177-239)
- **Menu Items**: Static links (Home, Products, Compare, Wishlist, Cart, Contact, Account)
- **Products Dropdown**: Dynamically generated from categories
- **No Menu Management**: No admin interface to manage menus

## Features to Implement

### 1. Database Structure

#### Tables to Create:

- **`menus`** - Store menu containers

  - `id` (INT, PRIMARY KEY)
  - `name` (VARCHAR) - Menu name (e.g., "Main Menu", "Footer Menu")
  - `slug` (VARCHAR, UNIQUE) - Menu identifier
  - `description` (TEXT) - Optional description
  - `created_at`, `updated_at` (TIMESTAMP)

- **`menu_items`** - Store individual menu items

  - `id` (INT, PRIMARY KEY)
  - `menu_id` (INT, FOREIGN KEY) - Parent menu
  - `parent_id` (INT, NULL) - For sub-menus (hierarchical)
  - `title` (VARCHAR) - Display text
  - `url` (VARCHAR) - Link URL
  - `type` (ENUM) - 'custom', 'page', 'category', 'product', 'post'
  - `object_id` (INT, NULL) - ID of linked object (page, category, etc.)
  - `target` (ENUM) - '\_self', '\_blank' (link target)
  - `css_classes` (VARCHAR) - Custom CSS classes
  - `icon` (VARCHAR) - Font Awesome icon class
  - `description` (TEXT) - Optional description
  - `sort_order` (INT) - Display order
  - `is_active` (TINYINT) - Active/inactive
  - `created_at`, `updated_at` (TIMESTAMP)

- **`menu_locations`** - Assign menus to theme locations
  - `id` (INT, PRIMARY KEY)
  - `location` (VARCHAR, UNIQUE) - Location slug (e.g., 'header', 'footer')
  - `menu_id` (INT, FOREIGN KEY) - Assigned menu
  - `description` (VARCHAR) - Location description
  - `updated_at` (TIMESTAMP)

### 2. Admin Interface

#### Pages to Create:

**A. Menu Management (`admin/menus.php`)**

- List all menus
- Create new menu
- Edit/Delete menus
- Assign menus to locations
- Quick actions (duplicate, export)

**B. Menu Editor (`admin/menu-edit.php`)**

- Drag-and-drop interface (using SortableJS or jQuery UI)
- Add menu items:
  - Custom Link (URL + Label)
  - Page (select from pages)
  - Category (select from categories)
  - Product (select from products)
  - Post (select from blog posts)
- Edit menu item properties:
  - Title/Label
  - URL
  - Link Target (same window/new tab)
  - CSS Classes
  - Icon (Font Awesome picker)
  - Description
- Hierarchical structure:
  - Drag items to create sub-menus
  - Visual indentation
  - Expand/collapse sub-menus
- Delete menu items
- Save menu structure

**C. Menu Locations (`admin/menu-locations.php`)**

- List available menu locations:
  - Header Menu (Primary Navigation)
  - Footer Menu
  - Mobile Menu
  - Sidebar Menu
  - Social Menu
- Assign menus to locations
- Preview menu assignments

### 3. Frontend Integration

#### Menu Helper Functions (`app/Helpers/MenuHelper.php`)

- `get_menu($menu_id_or_slug)` - Get menu by ID or slug
- `get_menu_by_location($location)` - Get menu assigned to location
- `render_menu($menu_id_or_slug, $options = [])` - Render menu HTML
- `menu_exists($menu_id_or_slug)` - Check if menu exists

#### Menu Renderer (`includes/menu.php`)

- Generate menu HTML structure
- Support for:
  - Multi-level dropdowns
  - Icons
  - Custom CSS classes
  - Active state detection
  - Mobile-responsive structure
- Preserve current styling and animations

### 4. Features

#### Menu Item Types:

1. **Custom Link** - Any URL with custom label
2. **Page** - Link to internal page
3. **Category** - Link to product category
4. **Product** - Link to specific product
5. **Post** - Link to blog post
6. **Post Category** - Link to blog category

#### Advanced Features:

- **Drag & Drop Ordering** - Reorder menu items visually
- **Hierarchical Menus** - Unlimited nesting levels
- **Menu Item Icons** - Font Awesome icon support
- **CSS Classes** - Add custom classes per item
- **Link Target** - Open in new tab option
- **Active State** - Auto-detect current page
- **Menu Locations** - Assign different menus to different areas
- **Menu Duplication** - Clone existing menus
- **Menu Export/Import** - Backup and restore menus

### 5. Migration Strategy

#### Preserve Current Menu:

1. Auto-create "Main Menu" on first setup
2. Import current hardcoded menu items:
   - Home → Custom Link
   - Products → Category dropdown (convert to menu structure)
   - Compare → Custom Link
   - Wishlist → Custom Link
   - Cart → Custom Link
   - Contact → Custom Link
   - Account → Custom Link (with sub-menu)

#### Backward Compatibility:

- Keep current menu code as fallback
- Show warning if no menu assigned to location
- Provide migration tool in admin

### 6. UI/UX Design

#### Menu Editor Interface:

- **Left Panel**: Menu items list (sortable)
- **Right Panel**: Menu item editor (when item selected)
- **Top Bar**: Menu name, save button, add item buttons
- **Visual Indicators**:
  - Drag handle
  - Parent/child relationships
  - Active/inactive status
  - Item type icons

#### Drag & Drop:

- Use SortableJS library (lightweight, no jQuery)
- Visual feedback during drag
- Auto-save on drop
- Undo/Redo functionality

### 7. Implementation Steps

#### Phase 1: Database & Models

1. Create database tables (SQL script)
2. Create `Menu` model (`app/Models/Menu.php`)
3. Create `MenuItem` model (`app/Models/MenuItem.php`)
4. Create `MenuLocation` model (`app/Models/MenuLocation.php`)

#### Phase 2: Admin Interface

1. Create `admin/menus.php` (menu list)
2. Create `admin/menu-edit.php` (menu editor)
3. Create `admin/menu-locations.php` (location assignment)
4. Add menu management to admin navigation

#### Phase 3: Frontend Integration

1. Create `app/Helpers/MenuHelper.php`
2. Create `includes/menu.php` (menu renderer)
3. Update `includes/header.php` to use new menu system
4. Add menu CSS for dropdowns and mobile

#### Phase 4: Migration & Testing

1. Create migration script
2. Import current menu structure
3. Test all menu types
4. Test hierarchical menus
5. Test menu locations

### 8. Technical Details

#### Libraries Needed:

- **SortableJS** - For drag-and-drop (CDN)
- **Font Awesome** - Already included
- **jQuery** - Optional (if needed for complex interactions)

#### CSS Framework:

- Use existing Tailwind CSS
- Custom CSS for menu editor
- Responsive menu styles

#### JavaScript:

- Vanilla JS for menu editor
- SortableJS for drag-and-drop
- AJAX for saving menu structure

### 9. File Structure

```
database/
  └── create-menu-system.sql

app/
  ├── Models/
  │   ├── Menu.php
  │   ├── MenuItem.php
  │   └── MenuLocation.php
  └── Helpers/
      └── MenuHelper.php

admin/
  ├── menus.php
  ├── menu-edit.php
  └── menu-locations.php

includes/
  ├── header.php (updated)
  └── menu.php (new)

assets/
  ├── css/
  │   └── menu-editor.css (new)
  └── js/
      └── menu-editor.js (new)
```

### 10. Success Criteria

✅ Create multiple menus
✅ Assign menus to different locations
✅ Add/edit/delete menu items
✅ Drag-and-drop reordering
✅ Create hierarchical menus (sub-menus)
✅ Different menu item types (custom, page, category, etc.)
✅ Menu appears correctly in frontend
✅ Mobile menu works
✅ Preserve current styling
✅ Backward compatible

## Estimated Implementation Time

- Phase 1: 2-3 hours
- Phase 2: 4-5 hours
- Phase 3: 2-3 hours
- Phase 4: 1-2 hours
  **Total: 9-13 hours**

## Notes

- Keep existing menu as fallback during transition
- Ensure mobile menu compatibility
- Maintain current design aesthetics
- Test with existing categories/products
