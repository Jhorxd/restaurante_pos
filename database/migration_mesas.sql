-- Gestión de mesas + enlace con ventas (ejecutar en phpMyAdmin o mysql CLI sobre restaurante_pos)
-- 1) Ejecutar el bloque CREATE.
-- 2) Si `ventas.id_mesa` aún no existe, ejecutar los tres ALTER de `ventas`.
--    Si MySQL devuelve error "Duplicate column", omitir esos ALTER.

CREATE TABLE IF NOT EXISTS `mesas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sucursal` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `capacidad` int(11) NOT NULL DEFAULT 4,
  `zona` varchar(80) DEFAULT NULL,
  `pos_orden` int(11) NOT NULL DEFAULT 0,
  `pos_x` int(11) NOT NULL DEFAULT 0,
  `pos_y` int(11) NOT NULL DEFAULT 0,
  `estado` enum('libre','ocupada','reservada','limpieza') NOT NULL DEFAULT 'libre',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `notas` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mesa_sucursal_codigo` (`id_sucursal`,`codigo`),
  KEY `id_sucursal` (`id_sucursal`),
  CONSTRAINT `mesas_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `ventas` ADD COLUMN `id_mesa` int(11) DEFAULT NULL AFTER `id_caja`;
ALTER TABLE `ventas` ADD KEY `ventas_ibfk_mesa` (`id_mesa`);
ALTER TABLE `ventas` ADD CONSTRAINT `ventas_ibfk_mesa` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id`) ON DELETE SET NULL;
