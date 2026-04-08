-- ============================================================
-- MIGRACIÓN: Sistema de Categorías Dinámicas
-- ============================================================

-- 1. Crear tabla de categorías
CREATE TABLE IF NOT EXISTS `categorias` (
  `id`             INT(11) NOT NULL AUTO_INCREMENT,
  `nombre`         VARCHAR(100) NOT NULL,
  `icono`          VARCHAR(50)  DEFAULT 'fa-tag',
  `color`          VARCHAR(20)  DEFAULT 'blue', -- emerald, violet, amber, blue, rose, etc.
  `comportamiento` ENUM('produccion', 'licores', 'cocteles') DEFAULT 'produccion',
  `orden`          INT(11) DEFAULT 0,
  `estado`         TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 2. Semillas: Insertar las categorías base actuales
INSERT INTO `categorias` (`nombre`, `icono`, `color`, `comportamiento`, `orden`) VALUES
('Producción', 'fa-utensils', 'emerald', 'produccion', 1),
('Licores',    'fa-wine-bottle', 'violet',  'licores',    2),
('Cócteles',   'fa-glass-martini-alt', 'amber',   'cocteles',   3);

-- 3. Agregar id_categoria a productos
ALTER TABLE `productos` 
  ADD COLUMN `id_categoria` INT(11) NULL AFTER `categoria`,
  ADD KEY `idx_producto_categoria` (`id_categoria`),
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id`) ON DELETE SET NULL;

-- 4. Migrar datos actuales: Asignar id_categoria según tipo_linea
UPDATE `productos` SET `id_categoria` = 1 WHERE `tipo_linea` = 'produccion';
UPDATE `productos` SET `id_categoria` = 2 WHERE `tipo_linea` = 'licores';
UPDATE `productos` SET `id_categoria` = 3 WHERE `tipo_linea` = 'cocteles';
