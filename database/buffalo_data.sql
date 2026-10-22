/*
MySQL Data Transfer
Source Host: localhost
Source Database: buffalo
Target Host: localhost
Target Database: buffalo
Date: 25/10/2011 02:13:06 a.m.
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for color
-- ----------------------------
DROP TABLE IF EXISTS `color`;
CREATE TABLE `color` (
  `id_color` int(11) NOT NULL auto_increment,
  `nombre` varchar(50) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_color`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for colorproducto
-- ----------------------------
DROP TABLE IF EXISTS `colorproducto`;
CREATE TABLE `colorproducto` (
  `id` int(11) NOT NULL auto_increment,
  `id_producto` int(11) NOT NULL,
  `id_color_1` int(11) NOT NULL,
  `id_color_2` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `colores_ibfk_1` (`id_producto`),
  KEY `fk_coloresproducto_color1` (`id_color_1`),
  CONSTRAINT `colorproducto_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for detalleproducto
-- ----------------------------
DROP TABLE IF EXISTS `detalleproducto`;
CREATE TABLE `detalleproducto` (
  `id_detalle_producto` int(11) NOT NULL auto_increment,
  `titulo` varchar(45) NOT NULL,
  `foto_detalle` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `id_producto` int(11) NOT NULL,
  PRIMARY KEY  (`id_detalle_producto`),
  KEY `fk_DetalleProducto_MasterProducto` (`id_producto`),
  CONSTRAINT `detalleproducto_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for master
-- ----------------------------
DROP TABLE IF EXISTS `master`;
CREATE TABLE `master` (
  `id_master` int(11) NOT NULL auto_increment,
  `clase` varchar(45) NOT NULL,
  `id_vehiculo` int(11) NOT NULL,
  PRIMARY KEY  (`id_master`),
  KEY `fk_Master_Vehiculo1` (`id_vehiculo`),
  CONSTRAINT `fk_Master_Vehiculo1` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculo` (`id_vehiculo`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for producto
-- ----------------------------
DROP TABLE IF EXISTS `producto`;
CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL auto_increment,
  `modelo` varchar(100) NOT NULL,
  `foto_principal` varchar(100) NOT NULL,
  `foto_zoom_1` varchar(100) NOT NULL,
  `foto_zoom_2` varchar(100) NOT NULL,
  `foto_zoom_3` varchar(100) NOT NULL,
  `foto_zoom_4` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `ficha_tecnica` varchar(100) NOT NULL,
  `id_master` int(11) NOT NULL,
  PRIMARY KEY  (`id_producto`),
  KEY `fk_Producto_Master1` (`id_master`),
  CONSTRAINT `fk_Producto_Master1` FOREIGN KEY (`id_master`) REFERENCES `master` (`id_master`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for usuario
-- ----------------------------
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id` int(11) NOT NULL auto_increment,
  `usuario` varchar(50) NOT NULL,
  `passwd` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for vehiculo
-- ----------------------------
DROP TABLE IF EXISTS `vehiculo`;
CREATE TABLE `vehiculo` (
  `id_vehiculo` int(11) NOT NULL auto_increment,
  `nombre` varchar(45) NOT NULL,
  PRIMARY KEY  (`id_vehiculo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `color` VALUES ('1', 'rojo', 'ff001e'), ('2', 'azul', '1500ff'), ('3', 'amarillo', 'ff992b');
INSERT INTO `color` VALUES ('4', 'negro', '000000');
INSERT INTO `color` VALUES ('5', 'gris', '777676');
INSERT INTO `colorproducto` VALUES ('1', '1', '1', '0'), ('2', '2', '2', '0'), ('3', '2', '1', '0'), ('4', '3', '2', '0'), ('5', '3', '1', '0'), ('6', '4', '1', '0'), ('7', '5', '2', '5'), ('8', '5', '2', '3'), ('9', '5', '1', '5'), ('10', '5', '1', '3'), ('11', '6', '2', '0'), ('12', '6', '1', '0'), ('13', '7', '2', '0'), ('14', '7', '1', '0'), ('15', '8', '2', '0'), ('16', '9', '3', '4'), ('17', '9', '5', '4'), ('18', '10', '4', '0'), ('19', '10', '2', '0');
INSERT INTO `detalleproducto` VALUES ('1', 'TANQUE DE CARBONO', 'images/detalle/productos_img1_small.jpg', 'Mayor durabilidad de tanque, además con este material en el tanque se impide la emisión de partículas de metal que sí se dan en los tanques convencionales acortando la vida del motor\r\n', '3'), ('2', 'PLANCHA DE ACERO PROTECTORA DE MOTOR', 'images/detalle/productos_img2_small.jpg', 'Protección al motor contra choques, piedras, etc. en caminos de dificil acceso.\r\n', '3'), ('3', 'SUSPENSION MONOSHOCK', 'images/detalle/productos_img2_small.jpg', 'Sistema de suspensión que permite mayor estabilidad y suavidad al conducir\r\n', '3'), ('4', '', 'images/detalle/bf300_small4_detalle.jpg', 'Estructura con chasis reforzado\r\n', '5'), ('5', '', 'images/detalle/bf300_small5_detalle.jpg', 'Timón con rodaje cónico\r\n', '5'), ('6', '', 'images/detalle/bf300_small6_detalle.jpg', 'Diseño moderno\r\n', '5'), ('7', '', 'images/detalle/b5_small3_detalle.jpg', 'Chapa  de encendido con seguridad antirrobo\r\n', '1'), ('8', '', 'images/detalle/b5_small4_detalle.jpg', 'Espacioso portaequipaje \r\n', '1'), ('9', '', 'images/detalle/b5_small5_detalle.jpg', 'Moderno y cómodo posapies\r\n', '1'), ('10', 'KIT CHACARERO ORIGINAL\r\n', 'images/detalle/b3ch_small3_detalle.jpg', 'Sólidos y originales . Protectores que brindan seguridad ante posibles choques\r\n', '2'), ('11', 'PARILLA POSTERIOR PROTECTORA\r\n', 'images/detalle/b3ch_small4_detalle.jpg', 'Para mayor comodidad y espacio.\r\n', '2'), ('12', '', 'images/detalle/b10_small3_detalle.jpg', 'Sistema de suspensión con gas nitrógeno\r\n', '9'), ('13', '', 'images/detalle/b10_small4_detalle.jpg', 'Tablero digital de batería, combustible y cambios\r\n', '9'), ('14', '', 'images/detalle/b10_small5_detalle.jpg', 'Tubo de escape con resonador y complemento protegido\r\n', '9'), ('15', '', 'images/detalle/b10_small6_detalle.jpg', 'Tanque con 21 lts de capacidad y diseño exclusivo.\r\n', '9'), ('16', '', 'images/detalle/b9_small3_detalle.jpg', 'Motor con sistema de inyección\r\n', '10'), ('17', '', 'images/detalle/b9_small4_detalle.jpg', 'Asiento ergonómico con diseño moderno\r\n', '10'), ('18', '', 'images/detalle/b9_small5_detalle.jpg', 'Luces oscilantes por 5 segundos al frenar\r\n', '10'), ('19', '', 'images/detalle/bf500_small3_detalle.jpg', 'Corona de una sola pieza mas grande  y reforzada\r\n', '6'), ('20', '', 'images/detalle/bf500_small4_detalle.jpg', 'Tolva de una sola pieza , estriada y de doble plancha\r\n', '6'), ('21', '', 'images/detalle/bf500_small5_detalle.jpg', 'Llanta mas grande 12/500\r\n', '6'), ('22', '', 'images/detalle/bf500_small6_detalle.jpg', 'Sistema de amortiguación con 7 hojas de muelle y resortes especiales\r\n', '6'), ('23', '', 'images/detalle/bf500_small3_detalle.jpg', 'Corona de una sola pieza mas grande  y reforzada\r\n', '7'), ('24', '', 'images/detalle/bf500_small4_detalle.jpg', 'Tolva de una sola pieza , estriada y de doble plancha\r\n', '7'), ('25', '', 'images/detalle/bf500_small5_detalle.jpg', 'Llanta mas grande 12/500\r\n', '7'), ('26', '', 'images/detalle/bf500_small6_detalle.jpg', 'Sistema de amortiguación con 7 hojas de muelle y resortes especiales\r\n', '7'), ('27', '', 'images/detalle/bf500_small3_detalle.jpg', 'Corona de una sola pieza mas grande  y reforzada\r\n', '8'), ('28', '', 'images/detalle/bf500_small4_detalle.jpg', 'Tolva de una sola pieza mas grande  y refozada\r\n', '8'), ('29', '', 'images/detalle/bf500_small5_detalle.jpg', 'Llanta mas grande 12/500\r\n', '8'), ('30', '', 'images/detalle/bf500_small6_detalle.jpg', 'Muelle con 7 hojas y resortes especiales\r\n', '8');
INSERT INTO `master` VALUES ('1', 'DE PASEO', '1'), ('2', 'DE TRABAJO', '1'), ('3', 'TODO TERRENO', '1'), ('4', 'PISTERAS', '1'), ('5', 'MODELO DE MOTOTAXI Y COLORES', '2'), ('6', 'DE CARGA', '3'), ('7', 'DE PASAJEROS', '3');
INSERT INTO `producto` VALUES ('1', 'B5', 'images/principal/productos_img_master_b5.jpg', 'images/zoom/b5_small1.jpg', 'images/zoom/b5_small2.jpg', '', '', 'Para quienes buscan algo mas que solo motos de paseo, la B5 se distingue por su acabado, modernas lÍneas de diseño, chapa de seguridad antirobos, pintura acrilica antirayadura, económica y de fácil manejo, hacen de la B5 una motocicleta lider en su categoría\r\n', 'ficha_tecnica/ficha.pdf', '1'), ('2', 'B3CH', 'images/principal/productos_img_master_b3ch.jpg', 'images/zoom/b3ch_small1.jpg', 'images/zoom/b3ch_small2.jpg', '', '', 'La B3CH cuenta con un motor de 150cc , que nos garantiza potencia para el trabajo de la ciudad y el campo. Esta versión BUFFALO viene con timón de rodaje cónico , garantizando un bajo costo de mantenimiento. Asi mismo cuenta con protectores originales que  proporcionan mayor seguridad al conductor\r\n', 'ficha_tecnica/ficha.pdf', '2'), ('3', 'B15', 'images/principal/productos_img_master_b15.jpg', 'images/zoom/productos_img1_small.jpg', 'images/zoom/productos_img2_small.jpg', '', '', 'La B15, una motocicleta diseñada para todo terreno y superar todas las exigencias de su conductor tanto para la ciudad y el campo, su alta performance y resistencia hacen de la B15 única y especial en su categoría\r\n', 'ficha_tecnica/ficha.pdf', '3'), ('4', 'B12', 'images/principal/productos_img_master_b12.jpg', 'images/zoom/productos_img1_small.jpg', 'images/zoom/productos_img2_small.jpg', '', '', 'La B12, tiene un potente motor de 200 cc para todo terreno, posee un sistema de motor con balanceador  que hace que reduzca al mínimo las vibraciones a altas velocidades, ideal para aquellos que buscan un diseño moderno con un potente motor\r\n', 'ficha_tecnica/ficha.pdf', '3'), ('5', 'BF300', 'images/principal/productos_img_master_mototaxi_bf300.jpg', 'images/zoom/bf300_small1.jpg', 'images/zoom/bf300_small2.jpg', 'images/zoom/bf300_small3.jpg', 'images/zoom/bf300_small1.jpg', 'El BF300 cuenta con una estructura reforzada, carenado con parabrisas panorámico, asientos mas confortables y un nuevo faro cuadrado que hace este vehículo lider en su categoría , además de la calidad de su motor , que garantiza un uso eficiente.\r\n', 'ficha_tecnica/ficha.pdf', '5'), ('6', 'BF500', 'images/principal/productos_img_master_furgon_bf500.jpg', 'images/zoom/bf500_small1.jpg', 'images/zoom/bf500_small2.jpg', '', '', 'El BF500 de carga es un vehículo ideal para  el transporte de productos, su estructura de doble plancha de acero y estriada permite maximizar su capacidad de carga sin perder potencia , además cuenta con sistema de inyectores para una mayor fuerza  y un moderno sistema de suspensión de 7 hojas de muelle y resortes especiales, que lo hace único en su categoría.\r\n', 'ficha_tecnica/ficha.pdf', '6'), ('7', 'BF600', 'images/principal/productos_img_master_furgon_bf600.jpg', 'images/zoom/bf600_small1.jpg', 'images/zoom/bf600_small2.jpg', '', '', 'El BF600 de carga, es un vehículo ideal para el transporte de productos, su estructura de doble plancha de acero y estriada permite maximizar su capacidad de carga sin perder potencia, ademas cuenta con un moderno sistema de suspensión de 7 hojas de muelle y resortes especiales que hace de este vehículo único en su categoría . Este modelo  de furgón cuenta con un moderno panel de lectura de indicadores en el tanque de combustible\r\n', 'ficha_tecnica/ficha.pdf', '6'), ('8', 'BF500 PASAJEROS', 'images/principal/productos_img_master_furgon_bf500pasajeros.jpg', 'images/zoom/bf500pasajero_small1.jpg', 'images/zoom/bf500pasajero_small2.jpg', '', '', 'El BF500 Pasajeros es el vehículo ideal para aquellos emprendedores que quieran maximizar su inversión. Su innovador diseño permite utilizarlo tanto para la carga de productos como para el transporte de pasajeros\r\n', 'ficha_tecnica/ficha.pdf', '7'), ('9', 'B10', 'images/principal/productos_img_master_b10.jpg', 'images/zoom/productos_img1_small.jpg', 'images/zoom/productos_img2_small.jpg', '', '', 'Nuestra B10 posee un diseño futurista, aerodinámico , moderno y por la calidad de su motor nos garantiza velocidad y potencia. Este modelo es exclusivo de BUFFALO MOTORS\r\n', 'ficha_tecnica/ficha.pdf', '4'), ('10', 'B9', 'images/principal/productos_img_master_b9.jpg', 'images/zoom/productos_img1_small.jpg', 'images/zoom/productos_img2_small.jpg', '', '', 'Este modelo posee un diseño innovador y versátil que la hace especial y atractiva, la tecnología desarrollada en su motor nos asegura el mayor rendimiento , tanto en ciudad como pista , además de tener un cómodo asiento que permite un manejo mas placentero\r\n', 'ficha_tecnica/ficha.pdf', '4');
INSERT INTO `usuario` VALUES ('1', 'root', 'root');
INSERT INTO `vehiculo` VALUES ('1', 'Motocicletas'), ('2', 'Mototaxis'), ('3', 'Furgon');
