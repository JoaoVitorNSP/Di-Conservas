-- Arquivo de inicialização completa do banco
-- Este arquivo será executado automaticamente pelo MySQL no docker-entrypoint-initdb.d

USE di_conservas;

-- configurações gerais
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

-- Inserir unidades de medida
INSERT INTO measurement_units (description) VALUES
('kg'),
('g'),
('ml'),
('L'),
('un'),
('pct'),
('cx')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Inserir categorias
INSERT INTO categories (description) VALUES
('Conservas'),
('Doces'),
('Molhos'),
('Temperos'),
('Legumes'),
('Frutas'),
('Especiais')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Produtos iniciais (baseados no products.json original)
INSERT INTO products (uuid, name, category_id, description, weight, unit_id, retail_price, wholesale_price, image) VALUES
(
  UUID(),
  'Broto de Bambu', 
  (SELECT id FROM categories WHERE description = 'Conservas' LIMIT 1),
  'Conserva de broto de bambu em conserva, ideal para pratos orientais e saladas.',
  0.300,
  (SELECT id FROM measurement_units WHERE description = 'kg' LIMIT 1),
  12.50,
  10.00,
  'assets/broto-de-bambu.jpeg'
),
(
  UUID(),
  'Espetinho Misto', 
  (SELECT id FROM categories WHERE description = 'Conservas' LIMIT 1),
  'Delicioso espetinho misto em conserva, perfeito para aperitivos e petiscos.',
  0.250,
  (SELECT id FROM measurement_units WHERE description = 'kg' LIMIT 1),
  8.90,
  7.50,
  'assets/espetinho-misto.jpeg'
),
(
  UUID(),
  'Mini Jiló', 
  (SELECT id FROM categories WHERE description = 'Legumes' LIMIT 1),
  'Mini jiló em conserva, sabor tradicional e textura crocante.',
  0.200,
  (SELECT id FROM measurement_units WHERE description = 'kg' LIMIT 1),
  6.75,
  5.50,
  'assets/mini-jilo.jpeg'
),
(
  UUID(),
  'Pimenta Caseira Picante', 
  (SELECT id FROM categories WHERE description = 'Temperos' LIMIT 1),
  'Pimenta caseira com ardência intensa, ideal para quem aprecia pratos bem temperados.',
  0.150,
  (SELECT id FROM measurement_units WHERE description = 'kg' LIMIT 1),
  9.80,
  8.00,
  'assets/pimenta-caseira-picante.jpeg'
),
(
  UUID(),
  'Pimenta Caseira Temperada', 
  (SELECT id FROM categories WHERE description = 'Temperos' LIMIT 1),
  'Pimenta caseira temperada com ervas especiais, equilibrio perfeito de sabor.',
  0.150,
  (SELECT id FROM measurement_units WHERE description = 'kg' LIMIT 1),
  9.80,
  8.00,
  'assets/pimenta-caseira-temperada.jpeg'
),
(
  UUID(),
  'Repolho Azedo', 
  (SELECT id FROM categories WHERE description = 'Legumes' LIMIT 1),
  'Repolho azedo fermentado naturalmente, rico em probióticos e sabor marcante.',
  0.400,
  (SELECT id FROM measurement_units WHERE description = 'kg' LIMIT 1),
  7.20,
  6.00,
  'assets/repolho-azedo.jpeg'
)
ON DUPLICATE KEY UPDATE 
  name = VALUES(name),
  category_id = VALUES(category_id),
  description = VALUES(description),
  weight = VALUES(weight),
  unit_id = VALUES(unit_id),
  retail_price = VALUES(retail_price),
  wholesale_price = VALUES(wholesale_price),
  image = VALUES(image);
