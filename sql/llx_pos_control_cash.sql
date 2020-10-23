-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 15-03-2020 a las 22:24:30
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
-- Estructura de tabla para la tabla `llx_pos_control_cash`
--

CREATE TABLE `llx_pos_control_cash` (
  `rowid` int(11) NOT NULL,
  `ref` varchar(30) NOT NULL,
  `entity` int(11) NOT NULL DEFAULT '1',
  `fk_cash` int(11) DEFAULT NULL,
  `fk_user` int(11) DEFAULT NULL,
  `date_c` datetime DEFAULT NULL,
  `type_control` tinyint(4) DEFAULT '0',
  `amount_teor` double(24,8) DEFAULT NULL,
  `amount_teor_card` double NOT NULL,
  `amount_real` double(24,8) DEFAULT NULL,
  `amount_real_card` double NOT NULL,
  `amount_diff` double(24,8) DEFAULT NULL,
  `amount_diff_card` double NOT NULL,
  `multicurrency_amount_teor` double(24,8) NOT NULL,
  `multicurrency_amount_teor_card` double NOT NULL,
  `multicurrency_amount_real` double(24,8) NOT NULL,
  `multicurrency_amount_real_card` double NOT NULL,
  `multicurrency_amount_diff` double(24,8) NOT NULL,
  `multicurrency_amount_diff_card` double NOT NULL,
  `amount_mov_out` double(24,8) DEFAULT NULL,
  `amount_mov_out_card` double NOT NULL,
  `amount_mov_int` double(24,8) DEFAULT NULL,
  `amount_mov_int_card` double NOT NULL,
  `amount_next_day` double(24,8) DEFAULT NULL,
  `amount_nex_day_card` double NOT NULL,
  `comment` text,
  `date_open` datetime DEFAULT NULL,
  `date_close` datetime DEFAULT NULL,
  `user_close` int(11) DEFAULT NULL,
  `fk_responsable` int(11) DEFAULT NULL,
  `fk_cierre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `llx_pos_control_cash`
--
ALTER TABLE `llx_pos_control_cash`
  ADD PRIMARY KEY (`rowid`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `llx_pos_control_cash`
--
ALTER TABLE `llx_pos_control_cash`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
