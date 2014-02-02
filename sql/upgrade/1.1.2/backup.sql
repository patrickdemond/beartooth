-- MySQL dump 10.13  Distrib 5.1.67, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: patrick_beartooth
-- ------------------------------------------------------
-- Server version	5.1.67-0ubuntu0.10.04.1

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
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `update_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(45) NOT NULL,
  `title` varchar(255) NOT NULL,
  `rank` int(10) unsigned DEFAULT NULL,
  `qnaire_specific` tinyint(1) NOT NULL,
  `parent_queue_id` int(10) unsigned DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_name` (`name`),
  UNIQUE KEY `uq_rank` (`rank`),
  KEY `fk_parent_queue_id` (`parent_queue_id`),
  CONSTRAINT `fk_queue_parent_queue_id` FOREIGN KEY (`parent_queue_id`) REFERENCES `queue` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue`
--

LOCK TABLES `queue` WRITE;
/*!40000 ALTER TABLE `queue` DISABLE KEYS */;
INSERT INTO `queue` VALUES (1,'2012-09-16 19:13:42','0000-00-00 00:00:00','all','All Participants',NULL,0,NULL,'All participants in the database.'),(2,'2012-09-16 19:13:42','0000-00-00 00:00:00','finished','Finished all questionnaires',NULL,0,1,'Participants who have completed all questionnaires.'),(3,'2012-09-16 19:13:42','0000-00-00 00:00:00','ineligible','Not eligible to answer questionnaires',NULL,0,1,'Participants who are not eligible to answer questionnaires due to a permanent\n      condition, because they are inactive or because they do not have a phone number.'),(4,'2012-09-16 19:13:42','0000-00-00 00:00:00','inactive','Inactive participants',NULL,0,3,'Participants who are not eligible for answering questionnaires because they have\n      been marked as inactive.'),(5,'2013-01-22 06:28:35','0000-00-00 00:00:00','refused consent','Participants who refused consent',NULL,0,3,'Participants who are not eligible for answering questionnaires because they have\n      refused consent.'),(6,'2013-10-18 04:55:23','0000-00-00 00:00:00','condition','Permanent Condition',NULL,0,3,'Participants who are not eligible for answering questionnaires because they have a permanent condition.'),(7,'2013-10-18 04:55:23','0000-00-00 00:00:00','eligible','Eligible to answer questionnaires',NULL,0,1,'Participants who are eligible to answer questionnaires.'),(8,'2013-10-18 04:55:23','0000-00-00 00:00:00','qnaire','Questionnaire',NULL,1,7,'Eligible participants who are currently assigned to the questionnaire.'),(9,'2013-10-18 04:55:23','0000-00-00 00:00:00','qnaire waiting','Waiting to begin',NULL,1,8,'Eligible participants who are waiting the scheduled cool-down period before\n      beginning the questionnaire.'),(10,'2013-10-18 04:55:23','0000-00-00 00:00:00','appointment','Appointment scheduled',NULL,1,8,'Participants whose interview has been scheduled.'),(11,'2013-10-18 04:55:23','0000-00-00 00:00:00','assigned','Currently Assigned',NULL,1,8,'Participants who are currently assigned to an interviewer.'),(12,'2013-10-18 04:55:23','0000-00-00 00:00:00','restricted','Restricted from calling',NULL,1,8,'Participants whose city, province or postcode have been restricted.'),(13,'2013-10-18 04:55:23','0000-00-00 00:00:00','quota disabled','Participant\'s quota is disabled',NULL,1,8,'Participants who belong to a quota which has been disabled'),(14,'2013-10-18 04:55:23','0000-00-00 00:00:00','outside calling time','Outside calling time',NULL,1,8,'Participants whose local time is outside of the valid calling hours.'),(15,'2013-10-18 04:55:23','0000-00-00 00:00:00','callback','Participants with callbacks',NULL,1,8,'Participants who have an (unassigned) callback.'),(16,'2013-10-18 04:55:23','0000-00-00 00:00:00','upcoming callback','Callback upcoming',NULL,1,15,'Participants who have an callback in the future.'),(17,'2013-10-18 04:55:23','0000-00-00 00:00:00','assignable callback','Callback assignable',1,1,15,'Participants who have an immediate callback which is ready to be assigned.'),(18,'2013-10-18 04:55:23','0000-00-00 00:00:00','new participant','Never assigned participants',NULL,1,8,'Participants who have never been assigned to an interviewer.'),(19,'2013-10-18 04:55:23','0000-00-00 00:00:00','new participant available','New participants, available',16,1,18,'New participants who are available.'),(20,'2013-10-18 04:55:23','0000-00-00 00:00:00','new participant not available','New participants, not available',17,1,18,'New participants who are not available.'),(21,'2013-10-18 04:55:23','0000-00-00 00:00:00','old participant','Previously assigned participants',NULL,1,8,'Participants who have been previously assigned.'),(22,'2013-10-18 04:55:23','0000-00-00 00:00:00','contacted','Last call: contacted',NULL,1,21,'Participants whose last call result was \'contacted\'.'),(23,'2013-10-18 04:55:23','0000-00-00 00:00:00','contacted waiting','Last call: contacted (waiting)',NULL,1,22,'Participants whose last call result was \'contacted\' and the scheduled call back\n      time has not yet been reached.'),(24,'2013-10-18 04:55:23','0000-00-00 00:00:00','contacted available','Last call: contacted (available)',2,1,22,'Available participants whose last call result was \'contacted\' and the scheduled call\n      back time has been reached.'),(25,'2013-10-18 04:55:23','0000-00-00 00:00:00','contacted not available','Last call: contacted (not available)',3,1,22,'Unavailable participants whose last call result was \'contacted\' and the scheduled call\n      back time has been reached.'),(26,'2013-10-18 04:55:23','0000-00-00 00:00:00','busy','Last call: busy line',NULL,1,21,'Participants whose last call result was \'busy\'.'),(27,'2013-10-18 04:55:23','0000-00-00 00:00:00','busy waiting','Last call: busy line (waiting)',NULL,1,26,'Participants whose last call result was \'busy\' and the scheduled call back\n      time has not yet been reached.'),(28,'2013-10-18 04:55:23','0000-00-00 00:00:00','busy available','Last call: busy (available)',4,1,26,'Available participants whose last call result was \'busy\' and the scheduled call\n      back time has been reached.'),(29,'2013-10-18 04:55:23','0000-00-00 00:00:00','busy not available','Last call: busy (not available)',5,1,26,'Unavailable participants whose last call result was \'busy\' and the scheduled call\n      back time has been reached.'),(30,'2013-10-18 04:55:23','0000-00-00 00:00:00','fax','Last call: fax line',NULL,1,21,'Participants whose last call result was \'fax\'.'),(31,'2013-10-18 04:55:23','0000-00-00 00:00:00','fax waiting','Last call: fax line (waiting)',NULL,1,30,'Participants whose last call result was \'fax\' and the scheduled call back\n      time has not yet been reached.'),(32,'2013-10-18 04:55:23','0000-00-00 00:00:00','fax available','Last call: fax (available)',6,1,30,'Available participants whose last call result was \'fax\' and the scheduled call \n      back time has been reached.'),(33,'2013-10-18 04:55:23','0000-00-00 00:00:00','fax not available','Last call: fax (not available)',7,1,30,'Unavailable participants whose last call result was \'fax\' and the scheduled call \n      back time has been reached.'),(34,'2013-10-18 04:55:23','0000-00-00 00:00:00','not reached','Last call: not reached',NULL,1,21,'Participants whose last call result was \'machine message\', \'machine no message\',\n      \'not reached\', \'disconnected\' or \'wrong number\'.'),(35,'2013-10-18 04:55:23','0000-00-00 00:00:00','not reached waiting','Last call: not reached (waiting)',NULL,1,34,'Participants whose last call result was \'machine message\', \'machine no message\',\n      \'not reached\', \'disconnected\' or \'wrong number\' and the scheduled call back time has not yet been\n      reached.'),(36,'2013-10-18 04:55:23','0000-00-00 00:00:00','not reached available','Last call: not reached (available)',8,1,34,'Available participants whose last call result was \'machine message\',\n      \'machine no message\', \'not reached\', \'disconnected\' or \'wrong number\' and the scheduled call\n      back time has been reached.'),(37,'2013-10-18 04:55:23','0000-00-00 00:00:00','not reached not available','Last call: not reached (not available)',9,1,34,'Unavailable participants whose last call result was \'machine message\',\n      \'machine no message\', \'not reached\', \'disconnected\' or \'wrong number\' and the scheduled\n      call back time has been reached.'),(38,'2013-10-18 04:55:23','0000-00-00 00:00:00','no answer','Last call: no answer',NULL,1,21,'Participants whose last call result was \'no answer\'.'),(39,'2013-10-18 04:55:23','0000-00-00 00:00:00','no answer waiting','Last call: no answer (waiting)',NULL,1,38,'Participants whose last call result was \'no answer\' and the scheduled call back\n      time has not yet been reached.'),(40,'2013-10-18 04:55:23','0000-00-00 00:00:00','no answer available','Last call: no answer (available)',10,1,38,'Available participants whose last call result was \'no answer\' and the scheduled call\n      back time has been reached.'),(41,'2013-10-18 04:55:23','0000-00-00 00:00:00','no answer not available','Last call: no answer (not available)',11,1,38,'Unavailable participants whose last call result was \'no answer\' and the scheduled call\n      back time has been reached.'),(42,'2013-10-18 04:55:23','0000-00-00 00:00:00','hang up','Last call: hang up',NULL,1,21,'Participants whose last call result was \'hang up\'.'),(43,'2013-10-18 04:55:23','0000-00-00 00:00:00','hang up waiting','Last call: hang up (waiting)',NULL,1,42,'Participants whose last call result was \'hang up\' and the scheduled call back\n      time has not yet been reached.'),(44,'2013-10-18 04:55:23','0000-00-00 00:00:00','hang up available','Last call: hang up (available)',12,1,42,'Available participants whose last call result was \'hang up\' and the scheduled call\n      back time has been reached.'),(45,'2013-10-18 04:55:23','0000-00-00 00:00:00','hang up not available','Last call: hang up (not available)',13,1,42,'Unavailable participants whose last call result was \'hang up\' and the scheduled call\n      back time has been reached.'),(46,'2013-10-18 04:55:23','0000-00-00 00:00:00','soft refusal','Last call: soft refusal',NULL,1,21,'Participants whose last call result was \'soft refusal\'.'),(47,'2013-10-18 04:55:23','0000-00-00 00:00:00','soft refusal waiting','Last call: soft refusal (waiting)',NULL,1,46,'Participants whose last call result was \'soft refusal\' and the scheduled call back\n      time has not yet been reached.'),(48,'2013-10-18 04:55:23','0000-00-00 00:00:00','soft refusal available','Last call: soft refusal (available)',14,1,46,'Available participants whose last call result was \'soft refusal\' and the scheduled call\n      back time has been reached.'),(49,'2013-10-18 04:55:23','0000-00-00 00:00:00','soft refusal not available','Last call: soft refusal (not available)',15,1,46,'Unavailable participants whose last call result was \'soft refusal\' and the scheduled\n      call back time has been reached.');
/*!40000 ALTER TABLE `queue` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-10-21 15:18:28