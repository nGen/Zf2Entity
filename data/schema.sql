--
-- Table structure for table `application_entity_statistics`
--

DROP TABLE IF EXISTS `application_entity_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `application_entity_statistics` (
  `entity_name` varchar(50) NOT NULL,
  `entity_primary_key` int(11) NOT NULL,
  `entity_ordering` int(11) NOT NULL,
  `views` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `locked` tinyint(1) NOT NULL,
  `locked_by` int(11) DEFAULT NULL,
  `locked_on` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `last_modified_by` int(11) NOT NULL,
  `last_modified_on` datetime NOT NULL,
  PRIMARY KEY (`entity_name`,`entity_primary_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

