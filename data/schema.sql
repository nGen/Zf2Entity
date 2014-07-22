-- --------------------------------------------------------

--
-- Table structure for table `application_entity_flags`
--

CREATE TABLE IF NOT EXISTS `application_entity_flags` (
  `entity_name` varchar(50) NOT NULL,
  `entity_primary_key` int(11) NOT NULL,
  `flag_id` int(11) NOT NULL,
  PRIMARY KEY (`entity_name`,`entity_primary_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `application_entity_logs`
--

CREATE TABLE IF NOT EXISTS `application_entity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_name` varchar(50) NOT NULL,
  `entity_primary_key` int(11) NOT NULL,
  `event_name` varchar(50) NOT NULL,
  `event_value` varchar(50) DEFAULT NULL,
  `event_time` datetime NOT NULL,
  `user` int(11) NOT NULL,
  `user_ip` varchar(39) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `application_entity_ratings`
--

CREATE TABLE IF NOT EXISTS `application_entity_ratings` (
  `entity_name` varchar(50) NOT NULL,
  `entity_primary_key` int(11) NOT NULL,
  `points` int(3) NOT NULL,
  `vote_ups` int(11) NOT NULL,
  `vote_downs` int(11) NOT NULL,
  PRIMARY KEY (`entity_name`,`entity_primary_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `application_entity_statistics`
--

CREATE TABLE IF NOT EXISTS `application_entity_statistics` (
  `entity_name` varchar(50) NOT NULL,
  `entity_primary_key` int(11) NOT NULL,
  `entity_ordering` int(11) NOT NULL,
  `views` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`entity_name`,`entity_primary_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `application_entity_tags`
--

CREATE TABLE IF NOT EXISTS `application_entity_tags` (
  `entity_name` varchar(50) NOT NULL,
  `entity_primary_key` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`entity_name`,`entity_primary_key`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `application_flags`
--

CREATE TABLE IF NOT EXISTS `application_flags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `application_resources`
--

CREATE TABLE IF NOT EXISTS `application_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `controller` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `application_resources_statistics`
--

CREATE TABLE IF NOT EXISTS `application_resources_statistics` (
  `resource_id` int(11) NOT NULL,
  `accesed_by` int(11) NOT NULL,
  `accesed_time` datetime NOT NULL,
  `response_code` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `application_tags`
--

CREATE TABLE IF NOT EXISTS `application_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;