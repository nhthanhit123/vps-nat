-- Add category column to vps_packages table
ALTER TABLE vps_packages 
ADD COLUMN category ENUM('nat', 'cheap') NOT NULL DEFAULT 'nat' 
AFTER selling_price;

-- Update existing packages to have appropriate categories
-- You can modify these queries based on your actual package names
UPDATE vps_packages 
SET category = 'nat' 
WHERE name LIKE '%NAT%' OR name LIKE '%nat%' OR selling_price < 100;

UPDATE vps_packages 
SET category = 'cheap' 
WHERE name LIKE '%Cheap%' OR name LIKE '%cheap%' OR selling_price >= 100;

-- Add index for better performance
ALTER TABLE vps_packages ADD INDEX idx_category (category);