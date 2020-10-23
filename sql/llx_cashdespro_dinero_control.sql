-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 15-03-2020 a las 22:24:01
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
-- Estructura de tabla para la tabla `llx_cashdespro_dinero_control`
--

CREATE TABLE `llx_cashdespro_dinero_control` (
  `rowid` int(11) NOT NULL,
  `fk_object` int(11) DEFAULT NULL,
  `fk_pago1` int(11) DEFAULT NULL,
  `fk_pago2` int(11) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `fk_bank` int(11) DEFAULT NULL,
  `fk_bank_2` int(11) DEFAULT NULL,
  `monto1` double(24,8) DEFAULT NULL,
  `monto2` double(24,8) DEFAULT NULL,
  `total_ttc` double(24,8) DEFAULT NULL,
  `cambio` double(24,8) DEFAULT NULL,
  `multicurrency_tx` double(24,8) DEFAULT NULL,
  `moneda2` varchar(5) DEFAULT NULL,
  `moneda1` varchar(5) DEFAULT NULL,
  `dolar_monto1` int(11) DEFAULT '0',
  `dolar_monto2` int(11) DEFAULT '0',
  `datec` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fk_user` int(11) DEFAULT NULL,
  `fk_cash` int(11) DEFAULT NULL,
  `fk_cierre` int(11) DEFAULT NULL,
  `metodo_pago1` varchar(10) DEFAULT NULL,
  `metodo_pago2` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `llx_cashdespro_dinero_control`
--
ALTER TABLE `llx_cashdespro_dinero_control`
  ADD PRIMARY KEY (`rowid`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `llx_cashdespro_dinero_control`
--
ALTER TABLE `llx_cashdespro_dinero_control`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
