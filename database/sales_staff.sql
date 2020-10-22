--
-- Table structure for table `sales_staff`
--

CREATE TABLE IF NOT EXISTS `sales_staff` (
  `staffid` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `email` varchar(200) NOT NULL,
  `active` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `enquiry` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `sale` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`staffid`),
  UNIQUE KEY `email` (`email`),
  KEY `active` (`active`),
  KEY `enquiry` (`enquiry`),
  KEY `sale` (`sale`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sales_staff_hours`
--

CREATE TABLE IF NOT EXISTS `sales_staff_hours` (
  `staffid` mediumint(8) UNSIGNED NOT NULL,
  `monday` time DEFAULT NULL,
  `tuesday` time DEFAULT NULL,
  `wednesday` time DEFAULT NULL,
  `thursday` time DEFAULT NULL,
  `friday` time DEFAULT NULL,
  `saturday` time DEFAULT NULL,
  `sunday` time DEFAULT NULL,
  `holiday` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`staffid`),
  KEY `holiday` (`holiday`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Constraints for table `sales_staff_hours`
--
ALTER TABLE `sales_staff_hours` ADD CONSTRAINT `sales_staff_hours_ibfk_1` FOREIGN KEY (`staffid`) REFERENCES `sales_staff` (`staffid`) ON DELETE CASCADE ON UPDATE CASCADE;