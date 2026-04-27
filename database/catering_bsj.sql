CREATE TABLE `users` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255),
  `email` VARCHAR(255) UNIQUE,
  `password` VARCHAR(255),
  `role` ENUM ('admin') DEFAULT 'admin',
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP
);

CREATE TABLE `menu_categories` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `description` TEXT,
  `sort_order` INT DEFAULT 0,
  `is_active` BOOLEAN DEFAULT true,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP
);

CREATE TABLE `menu_groups` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `menu_category_id` BIGINT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `description` TEXT,
  `sort_order` INT DEFAULT 0,
  `is_active` BOOLEAN DEFAULT true,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `created_by` BIGINT,
  `updated_by` BIGINT
);

CREATE TABLE `menu_items` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `menu_group_id` BIGINT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `base_price` DECIMAL(12,2) NOT NULL,
  `description` TEXT,
  `is_default` BOOLEAN DEFAULT false,
  `sort_order` INT DEFAULT 0,
  `is_active` BOOLEAN DEFAULT true,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `created_by` BIGINT,
  `updated_by` BIGINT
);

CREATE TABLE `menu_images` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `menu_item_id` BIGINT,
  `menu_group_id` BIGINT,
  `image_url` TEXT NOT NULL,
  `is_primary` BOOLEAN DEFAULT false,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP
);

CREATE TABLE `packages` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `price` DECIMAL(12,2) NOT NULL,
  `description` TEXT,
  `image_url` TEXT,
  `is_customizable` BOOLEAN DEFAULT true,
  `is_active` BOOLEAN DEFAULT true,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP
);

CREATE TABLE `package_items` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `package_id` BIGINT NOT NULL,
  `type` ENUM ('fixed', 'selectable_group') NOT NULL,
  `menu_item_id` BIGINT,
  `menu_group_id` BIGINT,
  `default_menu_item_id` BIGINT,
  `qty` INT DEFAULT 1,
  `min_select` INT,
  `max_select` INT
);

CREATE TABLE `orders` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `user_id` BIGINT,
  `order_code` VARCHAR(50) UNIQUE,
  `customer_name` VARCHAR(255),
  `phone` VARCHAR(20),
  `address` TEXT,
  `delivery_date` DATE,
  `time_slot` VARCHAR(100),
  `guest_count` INT,
  `subtotal` DECIMAL(12,2) DEFAULT 0,
  `discount` DECIMAL(12,2) DEFAULT 0,
  `shipping_cost` DECIMAL(12,2) DEFAULT 0,
  `grand_total` DECIMAL(12,2) DEFAULT 0,
  `status` ENUM ('waiting_payment', 'confirmed', 'processing', 'completed', 'canceled'),
  `notes` TEXT,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP
);

CREATE TABLE `order_items` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `order_id` BIGINT,
  `menu_item_id` BIGINT,
  `name_snapshot` VARCHAR(255),
  `price_snapshot` DECIMAL(12,2),
  `qty` INT,
  `subtotal` DECIMAL(12,2)
);

CREATE TABLE `payments` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `order_id` BIGINT,
  `type` ENUM ('dp', 'settlement', 'full'),
  `amount` DECIMAL(12,2),
  `status` ENUM ('pending', 'paid', 'rejected'),
  `method` ENUM ('transfer', 'cash', 'manual'),
  `input_source` ENUM ('user', 'admin'),
  `transaction_code` VARCHAR(100),
  `proof_image` TEXT,
  `note` TEXT,
  `paid_at` TIMESTAMP,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP,
  `verified_by` BIGINT
);

ALTER TABLE `menu_groups` ADD CONSTRAINT `fk_menu_groups_category` FOREIGN KEY (`menu_category_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL;

ALTER TABLE `menu_groups` ADD CONSTRAINT `fk_menu_groups_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `menu_groups` ADD CONSTRAINT `fk_menu_groups_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `menu_items` ADD CONSTRAINT `fk_menu_items_group` FOREIGN KEY (`menu_group_id`) REFERENCES `menu_groups` (`id`) ON DELETE SET NULL;

ALTER TABLE `menu_items` ADD CONSTRAINT `fk_menu_items_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `menu_items` ADD CONSTRAINT `fk_menu_items_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `menu_images` ADD CONSTRAINT `fk_menu_images_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

ALTER TABLE `menu_images` ADD CONSTRAINT `fk_menu_images_group` FOREIGN KEY (`menu_group_id`) REFERENCES `menu_groups` (`id`) ON DELETE CASCADE;

ALTER TABLE `package_items` ADD CONSTRAINT `fk_package_items_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

ALTER TABLE `package_items` ADD CONSTRAINT `fk_package_items_menu_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

ALTER TABLE `package_items` ADD CONSTRAINT `fk_package_items_menu_group` FOREIGN KEY (`menu_group_id`) REFERENCES `menu_groups` (`id`) ON DELETE CASCADE;

ALTER TABLE `package_items` ADD CONSTRAINT `fk_package_items_default_menu_item` FOREIGN KEY (`default_menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL;

ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_menu_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL;

ALTER TABLE `payments` ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

ALTER TABLE `payments` ADD CONSTRAINT `fk_payments_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
