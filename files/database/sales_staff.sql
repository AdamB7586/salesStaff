--
-- Table structure for table `sales_staff`
--

CREATE TABLE IF NOT EXISTS `sales_staff` (
  `salesstaffid` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `email` varchar(200) NOT NULL,
  `active` int(11) NOT NULL,
  `enquiry` int(11) NOT NULL,
  `sale` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`salesstaffid`),
  UNIQUE KEY `email` (`email`),
  KEY `active` (`active`),
  KEY `enquiry` (`enquiry`),
  KEY `sale` (`sale`)
);

--
-- Table structure for table `sales_staff_hours`
--

CREATE TABLE IF NOT EXISTS `sales_staff_hours` (
  `staffid` int(11) NOT NULL,
  `monday` time DEFAULT NULL,
  `tuesday` time DEFAULT NULL,
  `wednesday` time DEFAULT NULL,
  `thursday` time DEFAULT NULL,
  `friday` time DEFAULT NULL,
  `saturday` time DEFAULT NULL,
  `sunday` time DEFAULT NULL,
  `holiday` int(11) NOT NULL,
  PRIMARY KEY (`staffid`),
  KEY `holiday` (`holiday`)
)