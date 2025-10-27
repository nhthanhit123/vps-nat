-- Add apibanklink field to bank_accounts table
ALTER TABLE `bank_accounts` ADD COLUMN `apibanklink` text DEFAULT NULL AFTER `qr_code_url`;