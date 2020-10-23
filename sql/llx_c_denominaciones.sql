-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 15-03-2020 a las 22:27:11
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
-- Estructura de tabla para la tabla `llx_c_denominaciones`
--

CREATE TABLE `llx_c_denominaciones` (
  `rowid` int(11) NOT NULL,
  `code` varchar(45) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `muticurrency_code` varchar(5) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `label` varchar(45) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `llx_c_denominaciones`
--

INSERT INTO `llx_c_denominaciones` (`rowid`, `code`, `muticurrency_code`, `label`, `active`) VALUES
(1, '5', 'CRC', '5 colones', 1),
(2, '10', 'CRC', '10 colones', 1),
(3, '25', 'CRC', '25 colones', 1),
(4, '50', 'CRC', '50 colones', 1),
(5, '100', 'CRC', '100 colones', 1),
(6, '500', 'CRC', '500 colones', 1),
(7, '1000', 'CRC', '1000 colones', 1),
(8, '2000', 'CRC', '2000 colones', 1),
(9, '5000', 'CRC', '5000 colones', 1),
(10, '10000', 'CRC', '10000 colones', 1),
(11, '20000', 'CRC', '20000 colones', 1),
(12, '50000', 'CRC', '50000 colones', 1),
(13, '1', 'USD', '1 Dolar', 1),
(14, '2', 'USD', '2 Dolares', 1),
(20, '5', 'USD', '5 Dolares', 1),
(21, '10', 'USD', '10 Dolares', 1),
(22, '20', 'USD', '20 Dolares', 1),
(23, '50', 'USD', '50 Dolares', 1),
(24, '100', 'USD', '100 Dolares', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `llx_c_denominaciones`
--
ALTER TABLE `llx_c_denominaciones`
  ADD PRIMARY KEY (`rowid`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `llx_c_denominaciones`
--
ALTER TABLE `llx_c_denominaciones`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
