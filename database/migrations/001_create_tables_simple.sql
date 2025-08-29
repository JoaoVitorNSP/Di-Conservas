-- Migração inicial sem triggers
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- -----------------------------------------------------
-- Tabela: measurement_units
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS measurement_units (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  description VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (id),
  UNIQUE KEY ux_measurement_units_description (description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabela: categories
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  description VARCHAR(150) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (id),
  UNIQUE KEY ux_categories_description (description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabela: products (UUID será gerado pela aplicação)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid CHAR(36) DEFAULT NULL,         -- UUID será gerado pela aplicação
  name VARCHAR(255) NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  description TEXT,
  weight DECIMAL(10,3) DEFAULT NULL,
  unit_id INT UNSIGNED DEFAULT NULL,
  retail_price DECIMAL(13,2) NOT NULL DEFAULT 0.00,
  wholesale_price DECIMAL(13,2) NOT NULL DEFAULT 0.00,
  image VARCHAR(512) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (id),
  UNIQUE KEY ux_products_uuid (uuid),
  INDEX idx_products_name (name),
  INDEX idx_products_category (category_id),
  INDEX idx_products_unit (unit_id),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_products_unit FOREIGN KEY (unit_id) REFERENCES measurement_units(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
