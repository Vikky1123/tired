-- Add phone and country columns to users table if they don't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL AFTER full_name;
ALTER TABLE users ADD COLUMN IF NOT EXISTS country VARCHAR(50) NULL AFTER phone;

-- If the columns already exist but are not in the proper position, this won't cause errors
-- and the existing data will be preserved 