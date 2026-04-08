-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-04-2026 a las 17:30:16
-- Versión del servidor: 10.1.38-MariaDB
-- Versión de PHP: 7.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `restaurante_pos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajas`
--

CREATE TABLE `cajas` (
  `id` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `monto_apertura` decimal(10,2) DEFAULT NULL,
  `monto_cierre` decimal(10,2) DEFAULT NULL,
  `fecha_apertura` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `estado` enum('Abierta','Cerrada') DEFAULT 'Abierta'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cajas`
--

INSERT INTO `cajas` (`id`, `id_sucursal`, `id_usuario`, `monto_apertura`, `monto_cierre`, `fecha_apertura`, `fecha_cierre`, `estado`) VALUES
(1, 1, 1, '0.00', NULL, '2026-03-10 19:50:13', NULL, 'Abierta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `tipo_documento` enum('DNI','RUC','OTRO') DEFAULT 'DNI',
  `nro_documento` varchar(20) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `id_sucursal` int(1) NOT NULL DEFAULT '1',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `tipo_documento`, `nro_documento`, `telefono`, `email`, `direccion`, `estado`, `id_sucursal`, `fecha_registro`) VALUES
(1, 'Cliente Mostrador', 'DNI', '00000000', NULL, NULL, 'Venta al contado', 1, 1, '2026-03-10 20:34:24'),
(2, 'Juan Pérez', 'DNI', '12345678', '999111222', 'juan.perez@example.com', 'Av. Siempre Viva 123', 1, 1, '2026-03-10 20:34:24'),
(3, 'Empresa XYZ SAC', 'RUC', '20123456789', '014444555', 'contacto@xyz.com', 'Jr. Comercial 456, Lima', 1, 1, '2026-03-10 20:34:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `proveedor` varchar(200) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `id_sucursal`, `id_usuario`, `id_proveedor`, `proveedor`, `total`, `fecha_registro`) VALUES
(1, 1, 1, 1, '', '100.00', '2026-03-10 22:12:16'),
(2, 1, 1, 3, '', '50.00', '2026-03-10 22:49:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_detalle`
--

CREATE TABLE `compra_detalle` (
  `id` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kardex`
--

CREATE TABLE `kardex` (
  `id` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `tipo_movimiento` enum('Entrada','Salida') DEFAULT NULL,
  `motivo` enum('Venta','Compra','Ajuste','Traslado') DEFAULT NULL,
  `doc_tipo` enum('Venta','Compra','Ajuste') DEFAULT NULL,
  `doc_id` int(11) DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `stock_resultante` decimal(10,2) DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `kardex`
--

INSERT INTO `kardex` (`id`, `id_sucursal`, `id_producto`, `tipo_movimiento`, `motivo`, `doc_tipo`, `doc_id`, `cantidad`, `stock_resultante`, `fecha`) VALUES
(25, 1, 20, 'Salida', 'Venta', 'Venta', 18, '5.00', '4.00', '2026-04-05 04:09:08'),
(26, 1, 20, 'Salida', 'Venta', 'Venta', 19, '20.00', '0.00', '2026-04-05 04:09:15'),
(27, 1, 20, 'Salida', 'Venta', 'Venta', 20, '1.00', '5.00', '2026-04-05 04:58:02'),
(28, 1, 21, 'Salida', 'Venta', 'Venta', 25, '2.00', '8.00', '2026-04-05 05:21:07'),
(29, 1, 20, 'Salida', 'Venta', 'Venta', 27, '5.00', '4.00', '2026-04-05 05:26:13'),
(30, 1, 21, 'Salida', 'Venta', 'Venta', 27, '2.00', '6.00', '2026-04-05 05:26:13'),
(31, 1, 20, 'Salida', 'Venta', 'Venta', 28, '10.00', '2.00', '2026-04-05 17:17:36'),
(32, 1, 20, 'Salida', 'Venta', 'Venta', 30, '5.00', '1.00', '2026-04-05 17:29:08'),
(33, 1, 21, 'Salida', 'Venta', 'Venta', 30, '4.00', '2.00', '2026-04-05 17:29:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `capacidad` int(11) NOT NULL DEFAULT '4',
  `zona` varchar(80) DEFAULT NULL,
  `pos_orden` int(11) NOT NULL DEFAULT '0',
  `pos_x` int(11) NOT NULL DEFAULT '0',
  `pos_y` int(11) NOT NULL DEFAULT '0',
  `estado` enum('libre','ocupada','reservada','limpieza') NOT NULL DEFAULT 'libre',
  `id_venta_activa` int(11) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `notas` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `id_sucursal`, `codigo`, `nombre`, `capacidad`, `zona`, `pos_orden`, `pos_x`, `pos_y`, `estado`, `id_venta_activa`, `activo`, `notas`) VALUES
(1, 1, 'M01', 'MESA 01', 4, 'SALÓN', 1, 4, -5, 'libre', NULL, 1, ''),
(2, 1, 'M02', 'MESA VENTANA', 6, 'TERRAZA', 2, 6, -4, 'libre', NULL, 1, ''),
(3, 1, 'M03', 'MESA', 6, 'SALÓN', 3, 9, -3, 'libre', NULL, 1, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text,
  `categoria` varchar(100) DEFAULT NULL,
  `tipo_linea` enum('produccion','licores','cocteles') NOT NULL DEFAULT 'produccion',
  `id_licor_base` int(11) DEFAULT NULL,
  `repositorio_botellas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_repositorio_botellas` int(11) NOT NULL DEFAULT '5',
  `ventas_por_botella` int(11) NOT NULL DEFAULT '10',
  `contador_ventas_coctel` int(11) NOT NULL DEFAULT '0',
  `precio_compra` decimal(10,2) DEFAULT '0.00',
  `precio_venta` decimal(10,2) DEFAULT '0.00',
  `stock` decimal(10,2) DEFAULT '0.00',
  `imagen` varchar(255) DEFAULT NULL,
  `version` int(11) DEFAULT '0',
  `stock_minimo` int(11) DEFAULT '5',
  `id_sucursal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo_barras`, `nombre`, `descripcion`, `categoria`, `tipo_linea`, `id_licor_base`, `repositorio_botellas`, `max_repositorio_botellas`, `ventas_por_botella`, `contador_ventas_coctel`, `precio_compra`, `precio_venta`, `stock`, `imagen`, `version`, `stock_minimo`, `id_sucursal`) VALUES
(19, '00000000002', 'RED LABEL', '', '', 'licores', NULL, '0.00', 5, 10, 0, '10.00', '20.00', '90.00', NULL, 1775354897, 0, 1),
(20, '02132132132', 'COCTEL RED LABEL', '', '', 'cocteles', 19, '1.00', 5, 5, 1, '12.00', '25.00', '0.00', NULL, 1775354916, 0, 1),
(21, '02132132132', 'SALCHIPAPA', 'SALCHIPAPA', '', 'produccion', NULL, '0.00', 5, 10, 0, '5.00', '15.00', '2.00', NULL, 1775359180, 5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `razon_social` varchar(200) NOT NULL,
  `nombre_comercial` varchar(200) DEFAULT NULL,
  `tipo_documento` enum('DNI','RUC','OTRO') DEFAULT 'RUC',
  `nro_documento` varchar(20) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `rubro` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `id_sucursal` int(1) NOT NULL DEFAULT '1',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `razon_social`, `nombre_comercial`, `tipo_documento`, `nro_documento`, `telefono`, `email`, `direccion`, `rubro`, `estado`, `id_sucursal`, `fecha_registro`) VALUES
(1, 'Distribuidora Central SAC', 'DisCentral', 'RUC', '20601234567', '014567890', 'ventas@discentral.com', 'Av. Industrial 100, Lima', 'Abarrotes', 1, 1, '2026-03-10 20:34:32'),
(2, 'Bebidas del Sur SAC', 'Bebidas Sur', 'RUC', '20509876543', '013334444', 'contacto@bebidassur.com', 'Av. Los Licores 500, Lima', 'Bebidas', 1, 1, '2026-03-10 20:34:32'),
(3, 'Proveedor Genérico EIRL', 'Proveedor Genérico', 'RUC', '20445566779', '019998887', 'info@generico.com', 'Calle Mayorista 321, Lima', 'Varios', 1, 1, '2026-03-10 20:34:32'),
(4, 'Ronaldo Aldair Roman Salvador', 'Ronaldo Aldair Roman Salvador', 'DNI', '72715028', '933717307', 'Administrador@gmail.com', 'Lima', 'abarrotes', 1, 1, '2026-03-10 21:39:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursales`
--

CREATE TABLE `sucursales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` text,
  `estado` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `sucursales`
--

INSERT INTO `sucursales` (`id`, `nombre`, `direccion`, `estado`) VALUES
(1, 'Sede Principal', 'Lima', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','vendedor') DEFAULT 'vendedor',
  `id_sucursal` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `rol`, `id_sucursal`, `estado`) VALUES
(1, 'Administrador del Sistema', 'admin', '0192023a7bbd73250516f069df18b500', 'admin', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_caja` int(11) NOT NULL,
  `id_mesa` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','pagada','anulada') DEFAULT 'pagada',
  `metodo_pago` enum('efectivo','tarjeta','yape','plin','transferencia') NOT NULL DEFAULT 'efectivo',
  `monto_recibido` decimal(10,2) DEFAULT NULL,
  `vuelto` decimal(10,2) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `id_sucursal`, `id_usuario`, `id_cliente`, `id_caja`, `id_mesa`, `total`, `estado`, `metodo_pago`, `monto_recibido`, `vuelto`, `fecha_registro`) VALUES
(18, 1, 1, NULL, 1, NULL, '125.00', 'pagada', 'yape', '125.00', '0.00', '2026-04-05 04:09:08'),
(19, 1, 1, NULL, 1, NULL, '500.00', 'pagada', 'yape', '500.00', '0.00', '2026-04-05 04:09:15'),
(20, 1, 1, NULL, 1, 1, '25.00', 'pagada', 'tarjeta', '25.00', '0.00', '2026-04-05 04:58:02'),
(21, 1, 1, NULL, 1, 1, '100.00', 'pendiente', 'efectivo', '0.00', '0.00', '2026-04-05 05:11:48'),
(22, 1, 1, NULL, 1, 1, '20.00', 'pendiente', 'efectivo', '0.00', '0.00', '2026-04-05 05:12:24'),
(23, 1, 1, NULL, 1, 1, '250.00', 'pendiente', 'efectivo', '0.00', '0.00', '2026-04-05 05:12:50'),
(24, 1, 1, NULL, 1, 2, '30.00', 'pendiente', 'efectivo', '0.00', '0.00', '2026-04-05 05:19:53'),
(25, 1, 1, NULL, 1, 2, '30.00', 'pagada', 'yape', '30.00', '0.00', '2026-04-05 05:21:07'),
(26, 1, 1, NULL, 1, 2, '155.00', 'pendiente', 'efectivo', '0.00', '0.00', '2026-04-05 05:25:58'),
(27, 1, 1, NULL, 1, 2, '155.00', 'pagada', 'yape', '155.00', '0.00', '2026-04-05 05:26:13'),
(28, 1, 1, NULL, 1, 1, '250.00', 'pagada', 'yape', '250.00', '0.00', '2026-04-05 17:17:36'),
(29, 1, 1, NULL, 1, 1, '185.00', 'pendiente', 'efectivo', '0.00', '0.00', '2026-04-05 17:28:23'),
(30, 1, 1, NULL, 1, 1, '185.00', 'pagada', 'tarjeta', '185.00', '0.00', '2026-04-05 17:29:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_detalles`
--

CREATE TABLE `venta_detalles` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `venta_detalles`
--

INSERT INTO `venta_detalles` (`id`, `id_venta`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(18, 18, 20, '5.00', '25.00', '125.00'),
(19, 19, 20, '20.00', '25.00', '500.00'),
(20, 20, 20, '1.00', '25.00', '25.00'),
(21, 21, 20, '4.00', '25.00', '100.00'),
(22, 22, 19, '1.00', '20.00', '20.00'),
(23, 23, 20, '10.00', '25.00', '250.00'),
(24, 24, 21, '2.00', '15.00', '30.00'),
(25, 25, 21, '2.00', '15.00', '30.00'),
(26, 26, 21, '2.00', '15.00', '30.00'),
(27, 26, 20, '5.00', '25.00', '125.00'),
(28, 27, 20, '5.00', '25.00', '125.00'),
(29, 27, 21, '2.00', '15.00', '30.00'),
(30, 28, 20, '10.00', '25.00', '250.00'),
(33, 29, 20, '5.00', '25.00', '125.00'),
(34, 29, 21, '4.00', '15.00', '60.00'),
(35, 30, 20, '5.00', '25.00', '125.00'),
(36, 30, 21, '4.00', '15.00', '60.00');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `idx_doc` (`nro_documento`),
  ADD KEY `clientes_ibfk_sucursal` (`id_sucursal`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `compras_ibfk_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `compra_detalle`
--
ALTER TABLE `compra_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_compra` (`id_compra`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_mesa_sucursal_codigo` (`id_sucursal`,`codigo`),
  ADD KEY `id_sucursal` (`id_sucursal`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `id_licor_base` (`id_licor_base`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD KEY `idx_doc` (`nro_documento`),
  ADD KEY `proveedores_ibfk_sucursal` (`id_sucursal`);

--
-- Indices de la tabla `sucursales`
--
ALTER TABLE `sucursales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `id_sucursal` (`id_sucursal`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `id_caja` (`id_caja`),
  ADD KEY `ventas_ibfk_cliente` (`id_cliente`),
  ADD KEY `ventas_ibfk_mesa` (`id_mesa`);

--
-- Indices de la tabla `venta_detalles`
--
ALTER TABLE `venta_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_producto` (`id_producto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `compra_detalle`
--
ALTER TABLE `compra_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `kardex`
--
ALTER TABLE `kardex`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `sucursales`
--
ALTER TABLE `sucursales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `venta_detalles`
--
ALTER TABLE `venta_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD CONSTRAINT `cajas_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`),
  ADD CONSTRAINT `cajas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`);

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`),
  ADD CONSTRAINT `compras_ibfk_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

--
-- Filtros para la tabla `compra_detalle`
--
ALTER TABLE `compra_detalle`
  ADD CONSTRAINT `compra_detalle_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compra_detalle_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD CONSTRAINT `kardex_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`),
  ADD CONSTRAINT `kardex_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD CONSTRAINT `mesas_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_ibfk_licor` FOREIGN KEY (`id_licor_base`) REFERENCES `productos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `proveedores_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_caja`) REFERENCES `cajas` (`id`),
  ADD CONSTRAINT `ventas_ibfk_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `ventas_ibfk_mesa` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `venta_detalles`
--
ALTER TABLE `venta_detalles`
  ADD CONSTRAINT `venta_detalles_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `venta_detalles_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
