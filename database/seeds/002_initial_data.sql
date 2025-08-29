-- Dados iniciais para o sistema
SET NAMES utf8mb4;

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
