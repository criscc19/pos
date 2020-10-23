-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 15-03-2020 a las 22:23:19
-- Versión del servidor: 5.5.62-cll
-- Versión de PHP: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pruebase_prueba`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `llx_pagos_cash`
--

CREATE TABLE `llx_pagos_cash` (
  `rowid` int(11) NOT NULL,
  `fk_paiement_facture` int(11) NOT NULL,
  `datec` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fk_user_author` int(11) NOT NULL,
  `fk_caja` int(11) NOT NULL,
  `entity` int(11) NOT NULL,
  `fk_entrepot` int(11) NOT NULL,
  `fk_bank` int(11) NOT NULL,
  `fk_facture` int(11) NOT NULL,
  `fk_paiement` int(11) DEFAULT NULL,
  `fk_cierre` int(11) DEFAULT NULL,
  `metodo_pago` varchar(10) DEFAULT NULL,
  `monto` double(24,8) DEFAULT NULL,
  `multicurrency_code` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `llx_pagos_cash`
--
ALTER TABLE `llx_pagos_cash`
  ADD PRIMARY KEY (`rowid`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `llx_pagos_cash`
--
ALTER TABLE `llx_pagos_cash`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
