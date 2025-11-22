/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.24-MariaDB, for debian-linux-gnu (aarch64)
--
-- Host: localhost    Database: tcp_exercises
-- ------------------------------------------------------
-- Server version	10.6.24-MariaDB-ubu2204

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `EnunTCP`
--

DROP TABLE IF EXISTS `EnunTCP`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `EnunTCP` (
  `ExerciseID` int(11) NOT NULL,
  `ExerciseNum` int(11) DEFAULT NULL,
  `ExercisePart` int(11) DEFAULT NULL,
  `EnunTextES` text DEFAULT NULL,
  `EnunTextEN` text DEFAULT NULL,
  `congestion_control` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`ExerciseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Exercises`
--

DROP TABLE IF EXISTS `Exercises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Exercises` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ExerciseID` int(11) DEFAULT NULL,
  `Sender` int(11) DEFAULT NULL,
  `TicID` int(11) DEFAULT NULL,
  `SegmentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=308 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ExercisesDNS`
--

DROP TABLE IF EXISTS `ExercisesDNS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ExercisesDNS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ExerciseID` int(11) DEFAULT NULL,
  `TicID` int(11) DEFAULT NULL,
  `Sender` int(11) DEFAULT NULL,
  `Dest` int(11) DEFAULT NULL,
  `QR` tinyint(4) DEFAULT NULL,
  `RD` tinyint(4) DEFAULT NULL,
  `RA` tinyint(4) DEFAULT NULL,
  `AA` tinyint(4) DEFAULT NULL,
  `Qname` text DEFAULT NULL,
  `Qtype` text DEFAULT NULL,
  `Aname` text DEFAULT NULL,
  `Atype` text DEFAULT NULL,
  `Aaddr` text DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Segments`
--

DROP TABLE IF EXISTS `Segments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Segments` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SN` int(11) DEFAULT NULL,
  `AN` int(11) DEFAULT NULL,
  `SYN` int(11) DEFAULT NULL,
  `ACK` int(11) DEFAULT NULL,
  `FIN` int(11) DEFAULT NULL,
  `W` int(11) DEFAULT NULL,
  `MSS` int(11) DEFAULT NULL,
  `datalen` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=300 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `TcpState`
--

DROP TABLE IF EXISTS `TcpState`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `TcpState` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ExerciseID` int(11) DEFAULT NULL,
  `TicID` int(11) DEFAULT NULL,
  `Sender` int(11) DEFAULT NULL,
  `cwnd` decimal(10,2) DEFAULT NULL,
  `tcp_mode` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_ejercicios`
--

DROP TABLE IF EXISTS `menu_ejercicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_ejercicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(11) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `clave_idioma` varchar(50) DEFAULT NULL,
  `link_id` int(11) DEFAULT NULL,
  `part_num` int(11) DEFAULT NULL,
  `habilitado` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-22  5:40:57
