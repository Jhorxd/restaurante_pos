-- ============================================================
-- MIGRACIÓN: Sistema de Productos Compuestos (Recetas)
-- Fecha: 2026-04-08
-- ============================================================

-- 1. Agregar columna tiene_receta a productos
ALTER TABLE `productos`
  ADD COLUMN `tiene_receta` TINYINT(1) NOT NULL DEFAULT 0
  COMMENT '1 = producto compuesto (descuenta insumos), 0 = producto simple'
  AFTER `categoria`;

-- 2. Tabla de ingredientes / receta por producto
CREATE TABLE IF NOT EXISTS `producto_receta` (
  `id`           INT(11)        NOT NULL AUTO_INCREMENT,
  `id_producto`  INT(11)        NOT NULL  COMMENT 'Producto compuesto (ej: Salchipapa)',
  `id_insumo`    INT(11)        NOT NULL  COMMENT 'Ingrediente (ej: Papas)',
  `cantidad`     DECIMAL(10,4)  NOT NULL  COMMENT 'Cantidad del insumo por unidad vendida',
  `unidad`       VARCHAR(30)    DEFAULT NULL COMMENT 'Unidad informativa: kg, g, und, lt…',
  PRIMARY KEY (`id`),
  KEY `idx_receta_producto` (`id_producto`),
  KEY `idx_receta_insumo`   (`id_insumo`),
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_insumo`)   REFERENCES `productos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1
  COMMENT='Define los insumos que componen cada producto compuesto';

-- 3. Agregar columna nota en kardex para trazabilidad de insumos
ALTER TABLE `kardex`
  ADD COLUMN `nota` VARCHAR(255) DEFAULT NULL
  COMMENT 'Ej: Insumo de Salchipapa (x2)'
  AFTER `stock_resultante`;

-- ============================================================
-- EJEMPLO: Receta de Salchipapa (id_producto = 11)
-- Ajusta los id_insumo a los IDs reales de tus productos insumo
-- ============================================================
-- INSERT INTO `producto_receta` (id_producto, id_insumo, cantidad, unidad) VALUES
-- (11, 20, 0.2000, 'kg'),  -- Papas fritas
-- (11, 21, 0.1000, 'kg'),  -- Salchicha
-- (11, 22, 0.0500, 'kg'),  -- Ensalada
-- (11, 23, 1.0000, 'und'); -- Pan
