DROP TABLE IF EXISTS `o_customer_referee_relationships`;

CREATE TABLE `o_customer_referee_relationships` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
);

INSERT INTO `o_customer_referee_relationships` (`uid`, `name`, `status`) VALUES
(1, 'Mother', 1),
(2, 'Father', 1),
(3, 'Mother-in-law (MOM IN LAW)', 1),
(4, 'Father-in-law (DAD IN LAW)', 1),
(5, 'Son', 1),
(6, 'Daughter', 1),
(7, 'Brother', 1),
(8, 'Sister', 1),
(9, 'Sister-in-law (SIS IN LAW)', 1),
(10, 'Brother-in-law (BRO IN LAW)', 1),
(11, 'Husband', 1),
(12, 'Spouse', 1),
(13, 'Wife', 1),
(14, 'Cousin', 1),
(15, 'Niece', 1),
(16, 'Nephew', 1),
(17, 'Aunt', 1),
(18, 'Aunt-in-law', 1),
(19, 'Uncle', 1),
(20, 'Uncle-in-law', 1),
(21, 'Friend', 1),
(22, 'Grandmother', 1),
(23, 'Grandfather', 1),
(24, 'Other', 1),
(25, 'Daughter-in-law', 1),
(26, 'Son-in-law', 1);