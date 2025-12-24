    -- Migration Script: Import Current Hardcoded Menu
    -- This script creates a "Main Menu" with the current menu items

    -- Create Main Menu
    INSERT INTO `menus` (`name`, `slug`, `description`) VALUES
    ('Main Menu', 'main-menu', 'Primary navigation menu migrated from hardcoded menu')
    ON DUPLICATE KEY UPDATE `name` = 'Main Menu', `updated_at` = CURRENT_TIMESTAMP;

    -- Get the menu ID (handle both new insert and existing)
    SELECT @menu_id := id FROM menus WHERE slug = 'main-menu' LIMIT 1;

    -- Insert menu items
    INSERT INTO `menu_items` (`menu_id`, `parent_id`, `title`, `url`, `type`, `target`, `icon`, `sort_order`, `is_active`) VALUES
    (@menu_id, NULL, 'Home', '/', 'custom', '_self', 'fas fa-home', 1, 1),
    (@menu_id, NULL, 'Products', '/products.php', 'custom', '_self', 'fas fa-box', 2, 1),
    (@menu_id, NULL, 'Compare', '/compare.php', 'custom', '_self', 'fas fa-balance-scale', 3, 1),
    (@menu_id, NULL, 'Wishlist', '/wishlist.php', 'custom', '_self', 'fas fa-heart', 4, 1),
    (@menu_id, NULL, 'Cart', '/cart.php', 'custom', '_self', 'fas fa-shopping-cart', 5, 1),
    (@menu_id, NULL, 'Contact', '/contact.php', 'custom', '_self', 'fas fa-envelope', 6, 1);

    -- Assign to header location
    INSERT INTO `menu_locations` (`location`, `menu_id`, `description`) VALUES
    ('header', @menu_id, 'Primary Header Navigation')
    ON DUPLICATE KEY UPDATE `menu_id` = @menu_id;
