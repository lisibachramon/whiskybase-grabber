
CREATE SCHEMA IF NOT EXISTS whisky;
USE whisky;

CREATE TABLE IF NOT EXISTS `auction` (
  `auction_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(160) DEFAULT NULL,
  `auction` varchar(160) DEFAULT NULL,
  `category` varchar(160) DEFAULT NULL,
  `bottler` varchar(160) DEFAULT NULL,
  `serie` varchar(160) DEFAULT NULL,
  `vintage` varchar(160) DEFAULT NULL,
  `bottled` varchar(160) DEFAULT NULL,
  `casktype` varchar(160) DEFAULT NULL,
  `number` varchar(160) DEFAULT NULL,
  `strength` varchar(160) DEFAULT NULL,
  `size` varchar(160) DEFAULT NULL,
  `value` varchar(160) DEFAULT NULL,
  `url` varchar(300) NOT NULL,
  PRIMARY KEY (`auction_id`,`url`),
  UNIQUE KEY `url_UNIQUE` (`url`)
) ENGINE=InnoDB AUTO_INCREMENT=34192 DEFAULT CHARSET=latin1;


--
-- Table structure for table `whiskeybase`
--

CREATE TABLE IF NOT EXISTS `whiskeybase` (
  `whiskeybase_id` int(11) NOT NULL,
  `name` varchar(160) DEFAULT NULL,
  `description` varchar(800) DEFAULT NULL,
  `bottler` varchar(160) DEFAULT NULL,
  `category` varchar(160) DEFAULT NULL,
  `serie` varchar(160) DEFAULT NULL,
  `vintage` varchar(160) DEFAULT NULL,
  `bottled` varchar(160) DEFAULT NULL,
  `casktype` varchar(160) DEFAULT NULL,
  `number` varchar(160) DEFAULT NULL,
  `strength` varchar(160) DEFAULT NULL,
  `size` varchar(160) DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`whiskeybase_id`),
  FULLTEXT KEY `Fulltext` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Table structure for table `auction_has_whiskeybase`
--

CREATE TABLE IF NOT EXISTS `auction_has_whiskeybase` (
  `auction_has_whiskeybase_id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_auction_id` int(11) NOT NULL,
  `fk_whiskeybase_id` int(11) NOT NULL,
  `accuracy` float DEFAULT NULL,
  PRIMARY KEY (`auction_has_whiskeybase_id`),
  UNIQUE KEY `index2` (`fk_auction_id`,`fk_whiskeybase_id`),
  KEY `base_idx` (`fk_whiskeybase_id`),
  CONSTRAINT `auction` FOREIGN KEY (`fk_auction_id`) REFERENCES `auction` (`auction_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `base` FOREIGN KEY (`fk_whiskeybase_id`) REFERENCES `whiskeybase` (`whiskeybase_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=511 DEFAULT CHARSET=latin1;


