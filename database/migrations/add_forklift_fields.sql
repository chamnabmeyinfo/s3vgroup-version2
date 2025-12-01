-- Add forklift-specific fields to products table
-- Note: Run each ALTER TABLE statement separately to handle "column already exists" errors gracefully

-- Capacity and lifting specifications
ALTER TABLE products ADD COLUMN capacity DECIMAL(10,2) NULL COMMENT 'Lifting capacity in tons';
ALTER TABLE products ADD COLUMN lifting_height DECIMAL(10,2) NULL COMMENT 'Maximum lifting height in mm';
ALTER TABLE products ADD COLUMN mast_type VARCHAR(100) NULL COMMENT 'Mast type (e.g., Duplex, Triplex)';

-- Power and engine specifications
ALTER TABLE products ADD COLUMN power_type VARCHAR(50) NULL COMMENT 'Power type (Diesel, Electric, LPG, Gasoline)';
ALTER TABLE products ADD COLUMN engine_power VARCHAR(50) NULL COMMENT 'Engine power specification';
ALTER TABLE products ADD COLUMN battery_capacity VARCHAR(50) NULL COMMENT 'Battery capacity (for electric forklifts)';
ALTER TABLE products ADD COLUMN fuel_consumption VARCHAR(50) NULL COMMENT 'Fuel consumption rate';

-- Performance specifications
ALTER TABLE products ADD COLUMN max_speed DECIMAL(5,2) NULL COMMENT 'Maximum travel speed in km/h';
ALTER TABLE products ADD COLUMN turning_radius DECIMAL(5,2) NULL COMMENT 'Turning radius in mm';

-- Dimensions
ALTER TABLE products ADD COLUMN overall_length DECIMAL(8,2) NULL COMMENT 'Overall length in mm';
ALTER TABLE products ADD COLUMN overall_width DECIMAL(8,2) NULL COMMENT 'Overall width in mm';
ALTER TABLE products ADD COLUMN overall_height DECIMAL(8,2) NULL COMMENT 'Overall height in mm';
ALTER TABLE products ADD COLUMN wheelbase DECIMAL(8,2) NULL COMMENT 'Wheelbase in mm';

-- Additional specifications
ALTER TABLE products ADD COLUMN tire_type VARCHAR(50) NULL COMMENT 'Tire type (Pneumatic, Cushion, etc.)';
ALTER TABLE products ADD COLUMN manufacturer_model VARCHAR(100) NULL COMMENT 'Original manufacturer model number';
ALTER TABLE products ADD COLUMN year_manufactured INT NULL COMMENT 'Year of manufacture';
ALTER TABLE products ADD COLUMN warranty_period VARCHAR(50) NULL COMMENT 'Warranty period';
ALTER TABLE products ADD COLUMN country_of_origin VARCHAR(100) NULL COMMENT 'Country of origin';
ALTER TABLE products ADD COLUMN supplier_url VARCHAR(500) NULL COMMENT 'Original supplier product URL';

-- Add indexes for common searches (ignore errors if index already exists)
-- Note: These will be created by the migration script with error handling

