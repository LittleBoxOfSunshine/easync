-- MySQL dump 10.13  Distrib 5.5.46, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: easync
-- ------------------------------------------------------
-- Server version	5.5.46-0ubuntu0.14.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Auth_Token`
--

DROP TABLE IF EXISTS `Auth_Token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Auth_Token` (
  `auth_token` varchar(255) NOT NULL DEFAULT '0',
  `userID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`,`auth_token`),
  CONSTRAINT `Auth_Token_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `User` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Auth_Token`
--

LOCK TABLES `Auth_Token` WRITE;
/*!40000 ALTER TABLE `Auth_Token` DISABLE KEYS */;
INSERT INTO `Auth_Token` VALUES ('1a6db91e35032eaed8c399d8c8061105bceaae87e3c3bc1694b531da90c8dbb2',29),('4e21c1c62ad72e9c89571dbd2931c980187eca4f50130f38befe7ad8678e5a61',29),('546af5707b70fcc651e573002775503e68d7b65ca4d9ca1b187d0462ad6e51f0',29),('c5de40c1b3ce3227a6578381aef4a796f1b6d5d5794bd3d208bb2d247326618e',29),('e6d19b706739b2012fd9faf37c9ce519f9af5e526aa50f415debe6fd4069c8f3',29),('5815409f74a5a4a0d5e005ba105928a56e0e25db4a6867fbd34219cd42c89641',31);
/*!40000 ALTER TABLE `Auth_Token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CalendarTokens`
--

DROP TABLE IF EXISTS `CalendarTokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CalendarTokens` (
  `userID` int(11) NOT NULL DEFAULT '0',
  `platformID` varchar(255) NOT NULL DEFAULT '',
  `calID` varchar(255) NOT NULL DEFAULT '',
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`userID`,`platformID`,`calID`),
  CONSTRAINT `CalendarTokens_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `User` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `CalendarTokens`
--

LOCK TABLES `CalendarTokens` WRITE;
/*!40000 ALTER TABLE `CalendarTokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `CalendarTokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Contacts`
--

DROP TABLE IF EXISTS `Contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Contacts` (
  `contactEmail` varchar(255) NOT NULL DEFAULT '',
  `userID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`,`contactEmail`),
  CONSTRAINT `Contacts_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `User` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Contacts`
--

LOCK TABLES `Contacts` WRITE;
/*!40000 ALTER TABLE `Contacts` DISABLE KEYS */;
INSERT INTO `Contacts` VALUES ('test1@gmail.com',29),('test2@gmail.com',29),('test3@gmail.com',29),('test45@gmail.com',29),('test4@gmail.com',29);
/*!40000 ALTER TABLE `Contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Meeting`
--
DROP TABLE IF EXISTS `Event`;
DROP TABLE IF EXISTS `Meeting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Meeting` (
  `meetingID` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) DEFAULT NULL,
  `rsvp` boolean NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`meetingID`,`email`),
  KEY `email` (`email`),
  CONSTRAINT `Meeting_ibfk_1` FOREIGN KEY (`email`) REFERENCES `User` (`email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Meeting`
--

LOCK TABLES `Meeting` WRITE;
/*!40000 ALTER TABLE `Meeting` DISABLE KEYS */;
/*!40000 ALTER TABLE `Meeting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `MeetingDetails`
--

DROP TABLE IF EXISTS `EventDetails`;
DROP TABLE IF EXISTS `MeetingDetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MeetingDetails` (
  `location` varchar(255) DEFAULT NULL,
  `startTime` datetime DEFAULT NULL,
  `creationTime` datetime DEFAULT NULL,
  `updateTime` datetime DEFAULT NULL,
  `endTime` datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `creatorUserID` int(11) DEFAULT NULL,
  `timeZone` varchar(255) DEFAULT NULL,
  `recurrence` varchar(255) DEFAULT NULL,
  `attachments` varchar(255) DEFAULT NULL,
  `meetingID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`meetingID`),
  CONSTRAINT `MeetingDetails_ibfk_1` FOREIGN KEY (`meetingID`) REFERENCES `Meeting` (`meetingID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MeetingDetails`
--

LOCK TABLES `MeetingDetails` WRITE;
/*!40000 ALTER TABLE `MeetingDetails` DISABLE KEYS */;
/*!40000 ALTER TABLE `MeetingDetails` ENABLE KEYS */;
UNLOCK TABLES;




DROP TABLE IF EXISTS `EventGroups`;
DROP TABLE IF EXISTS `Group`;
DROP TABLE IF EXISTS `GroupDetails`;


DROP TABLE IF EXISTS `NearbyToken`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NearbyToken` (
  `creatorUserID` int(11) NOT NULL DEFAULT '0',
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`creatorUserID`,`token`),
  KEY `creatorUserID` (`creatorUserID`),
  CONSTRAINT `NearbyToken_ibfk_1` FOREIGN KEY (`creatorUserID`) REFERENCES `User` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `NearbyToken`
--

LOCK TABLES `NearbyToken` WRITE;
/*!40000 ALTER TABLE `NearbyToken` DISABLE KEYS */;
/*!40000 ALTER TABLE `NearbyToken` ENABLE KEYS */;
UNLOCK TABLES;

CREATE INDEX `indexToken` ON `NearbyToken` (`token`);


DROP TABLE IF EXISTS `NearbyAttendees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NearbyAttendees` (
  `userID` int(11) NOT NULL DEFAULT '0',
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`userID`,`token`),
  CONSTRAINT `NearbyAttendees_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `User` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `NearbyAttendees_ibfk_2` FOREIGN KEY (`token`) REFERENCES `NearbyToken` (`token`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `NearbyAttendees`
--

LOCK TABLES `NearbyAttendees` WRITE;
/*!40000 ALTER TABLE `NearbyAttendees` DISABLE KEYS */;
/*!40000 ALTER TABLE `NearbyAttendees` ENABLE KEYS */;
UNLOCK TABLES;







DROP TABLE IF EXISTS `Permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Permissions` (
  `userID` int(11) NOT NULL DEFAULT '0',
  `abilities` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`userID`),
  KEY `userID` (`userID`),
  CONSTRAINT `Permissions_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `Group` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Permissions`
--

LOCK TABLES `Permissions` WRITE;
/*!40000 ALTER TABLE `Permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `Permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Settings`
--

DROP TABLE IF EXISTS `Settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Settings` (
  `userID` int(11) NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY (`userID`),
  CONSTRAINT `Settings_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `User` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Settings`
--

LOCK TABLES `Settings` WRITE;
/*!40000 ALTER TABLE `Settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `Settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `User` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phoneNumber` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `authToken` varchar(255) DEFAULT NULL,
  `passwordHash` varchar(255) DEFAULT NULL,
  `passwordSalt` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `User`
--

LOCK TABLES `User` WRITE;
/*!40000 ALTER TABLE `User` DISABLE KEYS */;
INSERT INTO `User` VALUES (17,'John Doe','test2@gmail.com',NULL,NULL,NULL,'$2y$10$nd7.ryve7H0LxZRmmj7NJ.dAsdEKDlH2xC3gWR04mERRRDFBT9da6','Þþ¯+Þì}Å”fš>Í\'ì§sŒf'),(21,'Jice Rapper','testit@gmail.com',NULL,NULL,NULL,'$2y$10$471BlcYhtIDd28MUHjT6E..6v7tLTjxhYcZrW2fdvj9KadZ973iMG','ã½A•Æ!´€ÝÛÃ4úå¢¹Äo'),(23,'Jayce Miller','jayce@gmail.com',NULL,NULL,NULL,'$2y$10$winBtqmggYyOv10nVER4q.OkyPM0GR.b6Gphs11U4dawUzMH4h3lm','Â)Á¶© ŒŽ¿]\'TDx¨Zúö\"MG'),(24,'Bob Smith','testytest@gmail.com',NULL,NULL,NULL,'$2y$10$mqSzDagfFFzxmqk24FJxjukEome.8blJ/1Q6wl48ADEqfVaCtl0ZK','š¤³\r¨\\ñš©6àRq\'“ª¤Aë'),(29,'Bob Smith','newtest@gmail.com',NULL,NULL,NULL,'$2y$10$HRjRbcZzt0.EnRwFDtUGHuuJl3INut1UtwzW3ile0U90DuZBuw3cm','ÑmÆs·O„ÕneÙ$Øý'),(30,'bobby doe','newtest2@gmail.com',NULL,NULL,NULL,'$2y$10$aD10NOO0YK7HSog550goMO1Hn2WajBnYsdE9gO69OwH2Jkp5ZHnbW','h=t4ã´`®ÇJˆ9çH(1\nø-¹ž4'),(31,'Jayce Miller','jaycem@gmail.com',NULL,NULL,NULL,'$2y$10$7WpP3b1Qy743VRmVEXVuhegHdD0rzu3ukhyVOY7roI3HPLiE9yRci','íjOÝ½PË¾7U•un†bêøï');
/*!40000 ALTER TABLE `User` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-12-02 21:53:57
