-- Configurações gerais
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- -----------------------------------------------------
-- Tabela: holes (papéis)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS holes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  description VARCHAR(100) NOT NULL, -- nome do papel
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (id),
  UNIQUE KEY ux_holes_description (description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabela: users
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id CHAR(36) NOT NULL,       -- UUID (chave primária)
  hole_id INT UNSIGNED DEFAULT NULL, -- FK para holes (papel)
  name VARCHAR(200) NOT NULL,
  email VARCHAR(254) NOT NULL,
  password VARCHAR(255) NOT NULL, -- hash da senha
  phone VARCHAR(40) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (id),
  UNIQUE KEY ux_users_email (email),
  INDEX idx_users_name (name),
  CONSTRAINT fk_users_hole FOREIGN KEY (hole_id) REFERENCES holes(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger para preencher id UUID em users quando não passado
DELIMITER $$
CREATE TRIGGER trg_users_before_insert
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
  IF NEW.id IS NULL OR NEW.id = '' THEN
    SET NEW.id = UUID();
  END IF;
END$$
DELIMITER ;

-- Inserir papéis padrão
INSERT INTO holes (description) VALUES 
('Administrador'), 
('Gerente'), 
('Funcionário')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Inserir usuário administrador padrão
INSERT INTO users (id, hole_id, name, email, password, status) VALUES 
(UUID(), 1, 'Administrador', 'admin@diconservas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name);
-- Senha padrão: password (hash bcrypt)
