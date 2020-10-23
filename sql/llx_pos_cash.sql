-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 15-03-2020 a las 22:28:56
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
-- Estructura de tabla para la tabla `llx_pos_cash`
--

CREATE TABLE `llx_pos_cash` (
  `rowid` int(11) NOT NULL,
  `entity` int(11) NOT NULL DEFAULT '1',
  `code` varchar(3) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `tactil` tinyint(4) NOT NULL DEFAULT '0',
  `barcode` tinyint(4) NOT NULL DEFAULT '0',
  `fk_paycash` int(11) DEFAULT NULL,
  `fk_modepaycash` int(11) DEFAULT NULL,
  `fk_paybank` int(11) DEFAULT NULL,
  `fk_modepaybank` int(11) DEFAULT NULL,
  `fk_warehouse` int(11) DEFAULT NULL,
  `fk_device` int(11) DEFAULT NULL,
  `fk_soc` int(11) DEFAULT NULL,
  `is_used` tinyint(4) DEFAULT '0',
  `fk_user_u` int(11) DEFAULT NULL,
  `fk_user_c` int(11) DEFAULT NULL,
  `fk_user_m` int(11) DEFAULT NULL,
  `datec` datetime DEFAULT NULL,
  `datea` datetime DEFAULT NULL,
  `is_closed` tinyint(4) DEFAULT '0',
  `fk_modepaybank_extra` int(11) DEFAULT NULL,
  `fk_paybank_extra` int(11) DEFAULT NULL,
  `bank_active` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `llx_pos_cash`
--
ALTER TABLE `llx_pos_cash`
  ADD PRIMARY KEY (`rowid`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `llx_pos_cash`
--
ALTER TABLE `llx_pos_cash`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
