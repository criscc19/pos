-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 11-08-2019 a las 01:12:46
-- Versión del servidor: 5.7.24
-- Versión de PHP: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ng9`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `llx_pos_ticket`
--

CREATE TABLE IF NOT EXISTS `llx_pos_ticket` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `ticketnumber` varchar(30) COLLATE utf8_spanish_ci NOT NULL,
  `type` int(11) DEFAULT NULL,
  `entity` int(11) NOT NULL DEFAULT '1',
  `fk_cash` int(11) NOT NULL,
  `fk_soc` int(11) NOT NULL,
  `fk_place` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_ticket` date DEFAULT NULL,
  `date_closed` datetime DEFAULT NULL,
  `tms` timestamp NULL DEFAULT NULL,
  `paye` smallint(6) NOT NULL DEFAULT '0',
  `remise_percent` double DEFAULT '0',
  `remise_absolute` double DEFAULT '0',
  `remise` double DEFAULT '0',
  `customer_pay` double(24,8) DEFAULT '0.00000000',
  `difpayment` double(24,8) DEFAULT '0.00000000',
  `tva` double(24,8) DEFAULT '0.00000000',
  `localtax1` double(24,8) DEFAULT '0.00000000',
  `localtax2` double(24,8) DEFAULT '0.00000000',
  `total_ht` double(24,8) DEFAULT '0.00000000',
  `total_ttc` double(24,8) DEFAULT '0.00000000',
  `multicurrency_total_ht` float(24,8) DEFAULT NULL,
  `multicurrency_total_tva` float(24,8) DEFAULT NULL,
  `multicurrency_total_ttc` float(24,8) DEFAULT NULL,
  `fk_statut` smallint(6) NOT NULL DEFAULT '0',
  `fk_user_author` int(11) DEFAULT NULL,
  `fk_user_close` int(11) DEFAULT NULL,
  `fk_facture` int(11) DEFAULT NULL,
  `fk_ticket_source` int(11) DEFAULT NULL,
  `fk_mode_reglement` int(11) DEFAULT NULL,
  `fk_control` int(11) DEFAULT NULL,
  `note` text COLLATE utf8_spanish_ci,
  `note_public` text COLLATE utf8_spanish_ci,
  `model_pdf` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `import_key` varchar(14) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`rowid`),
  KEY `idx_ticket_fk_soc` (`fk_soc`),
  KEY `idx_ticket_fk_user_author` (`fk_user_author`),
  KEY `idx_ticket_fk_ticket_source` (`fk_ticket_source`),
  KEY `idx_ticket_fk_place` (`fk_place`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `llx_pos_ticket`
--
ALTER TABLE `llx_pos_ticket`
  ADD CONSTRAINT `fk_ticket_fk_place` FOREIGN KEY (`fk_place`) REFERENCES `llx_pos_places` (`rowid`),
  ADD CONSTRAINT `fk_ticket_fk_soc` FOREIGN KEY (`fk_soc`) REFERENCES `llx_societe` (`rowid`),
  ADD CONSTRAINT `fk_ticket_fk_user_author` FOREIGN KEY (`fk_user_author`) REFERENCES `llx_user` (`rowid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
