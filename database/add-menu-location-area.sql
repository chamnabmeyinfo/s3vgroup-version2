-- Add area field to menu_locations table
-- This allows categorizing menu locations by area (Top Header, Main, Footer, etc.)
-- Run this file via phpMyAdmin or MySQL command line

-- Check if area column exists, if not add it
SET @dbname = DATABASE();
SET @tablename = 'menu_locations';
SET @columnname = 'area';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` VARCHAR(50) DEFAULT ''main'' AFTER `location`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index if it doesn't exist (check first)
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = 'idx_area')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX `idx_area` (`area`)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing locations with default areas (only if area is NULL or empty)
UPDATE `menu_locations` SET `area` = 'header' WHERE `location` IN ('header', 'mobile', 'social') AND (`area` IS NULL OR `area` = '');
UPDATE `menu_locations` SET `area` = 'footer' WHERE `location` = 'footer' AND (`area` IS NULL OR `area` = '');
UPDATE `menu_locations` SET `area` = 'sidebar' WHERE `location` = 'sidebar' AND (`area` IS NULL OR `area` = '');
UPDATE `menu_locations` SET `area` = 'main' WHERE `area` IS NULL OR `area` = '';
