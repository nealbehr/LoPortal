
/*!40000 DROP DATABASE IF EXISTS `loportal_test`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `loportal_test` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `loportal_test`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `address`;
CREATE TABLE `address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `place_id` varchar(100) DEFAULT NULL,
  `formatted_address` varchar(255) DEFAULT NULL,
  `street_number` varchar(64) DEFAULT NULL,
  `street` varchar(64) DEFAULT NULL,
  `city` varchar(30) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `address` (`id`, `place_id`, `formatted_address`, `street_number`, `street`, `city`, `state`, `postal_code`, `created_at`, `updated_at`) VALUES
(1,	'ChIJoc9oaoY8TIYRJhHyHVGr3A8',	'Dallas Pkwy, Texas, USA',	NULL,	'Dallas Pkwy',	NULL,	'TX',	NULL,	'2015-06-05 14:54:52',	'2015-06-02 21:37:57'),
(2,	'ChIJoc9oaoY8TIYRJhHyHVGr3A8',	'Dallas Pkwy, Dallas, TX, USA',	NULL,	'Dallas Pkwy',	'Dallas',	'TX',	NULL,	'2015-06-05 14:55:39',	'2015-06-05 14:55:39');

DROP TABLE IF EXISTS `lender`;
CREATE TABLE `lender` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `lender` (`id`, `name`, `picture`, `created_at`, `updated_at`) VALUES
(1,	'Hana Small Business Lending, Inc.',	'/images/img01.png',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(2,	'Colorado Lending Source, Ltd.',	'/images/img01.png',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00'),
(3,	'USA Lending, LLC',	'/images/img01.png',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00');

DROP TABLE IF EXISTS `lender_disclosure`;
CREATE TABLE `lender_disclosure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lender_id` int(11) unsigned NOT NULL,
  `state` varchar(2) DEFAULT NULL,
  `disclosure` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lender_id` (`lender_id`,`state`),
  CONSTRAINT `lender_disclosure_ibfk_1` FOREIGN KEY (`lender_id`) REFERENCES `lender` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `lender_disclosure` (`id`, `lender_id`, `state`, `disclosure`, `created_at`, `updated_at`) VALUES
(1,	1,	'US',	'Not all applicants will qualify. Some products offered by ABC Lending include modified documentation requirements and may have a higher interest rate, more points or more fees than other products requiring documentation. Minimum FICO, reserve, and other requirements apply. Contact your Loan Officer for additional program guidelines, restrictions, and eligibility requirements. Rates, points, APR’s and programs are subject to change at any time until locked-in. NMLS #2900437',	'2015-06-02 15:28:45',	NULL),
(2,	2,	'US',	'Not all applicants will qualify. Some products offered by ABC Lending include modified documentation requirements and may have a higher interest rate, more points or more fees than other products requiring documentation. Minimum FICO, reserve, and other requirements apply. Contact your Loan Officer for additional program guidelines, restrictions, and eligibility requirements. Rates, points, APR’s and programs are subject to change at any time until locked-in. NMLS #2900437',	'2015-06-02 15:28:45',	NULL),
(3,	3,	'US',	'Not all applicants will qualify. Some products offered by ABC Lending include modified documentation requirements and may have a higher interest rate, more points or more fees than other products requiring documentation. Minimum FICO, reserve, and other requirements apply. Contact your Loan Officer for additional program guidelines, restrictions, and eligibility requirements. Rates, points, APR’s and programs are subject to change at any time until locked-in. NMLS #2900437',	'2015-06-02 15:28:45',	NULL);

DROP TABLE IF EXISTS `phinxlog`;
CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `phinxlog` (`version`, `start_time`, `end_time`) VALUES
(20150313131858,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150313140430,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150320131838,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150320145555,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150322150315,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150325130241,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150325130855,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150325135542,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150326182953,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150327115712,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150331095620,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150402084505,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150406171634,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150408122518,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150408123110,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150408123454,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150408124634,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150410162510,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150411113007,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150411113625,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150411113909,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150411160020,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150414154418,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150422160035,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150427114812,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150503075843,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150505141900,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150509151829,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150511130237,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150513113111,	'2015-05-15 18:53:18',	'2015-05-15 18:53:18'),
(20150515133414,	'2015-06-02 15:28:45',	'2015-06-02 15:28:45'),
(20150519093318,	'2015-06-02 15:28:45',	'2015-06-02 15:28:45'),
(20150522082811,	'2015-06-02 15:28:45',	'2015-06-02 15:28:45'),
(20150523143834,	'2015-06-02 15:28:45',	'2015-06-02 15:28:45'),
(20150525102352,	'2015-06-02 15:28:45',	'2015-06-02 15:28:45'),
(20150528120536,	'2015-06-02 15:28:45',	'2015-06-02 15:28:45'),
(20150528122526,	'2015-06-02 15:28:45',	'2015-06-02 15:28:45'),
(20150528133409,	'2015-06-02 15:28:45',	'2015-06-02 15:28:45'),
(20150530092141,	'2015-06-02 15:28:45',	'2015-06-02 15:28:45'),
(20150530133521,	'2015-06-02 15:28:45',	'2015-06-02 15:28:45'),
(20150601085308,	'2015-06-02 15:28:45',	'2015-06-02 15:28:46'),
(20150604140934,	'2015-06-05 19:34:25',	'2015-06-05 19:34:25'),
(20150608093130,	'2015-06-08 22:58:28',	'2015-06-08 22:58:29'),
(20150608144156,	'2015-06-08 22:58:29',	'2015-06-08 22:58:29'),
(20150610125838,	'2015-07-15 13:36:57',	'2015-07-15 13:36:57'),
(20150617055514,	'2015-07-15 13:36:57',	'2015-07-15 13:36:57'),
(20150617063440,	'2015-07-15 13:37:38',	'2015-07-15 13:37:38'),
(20150623123702,	'2015-07-15 13:37:38',	'2015-07-15 13:37:38'),
(20150630143846,	'2015-07-15 13:37:38',	'2015-07-15 13:37:38'),
(20150701163459,	'2015-07-15 13:38:02',	'2015-07-15 13:38:02'),
(20150702082907,	'2015-07-15 13:38:02',	'2015-07-15 13:38:02'),
(20150703094446,	'2015-07-15 13:38:02',	'2015-07-15 13:38:02'),
(20150703112552,	'2015-07-15 13:38:02',	'2015-07-15 13:38:02'),
(20150703112902,	'2015-07-15 13:38:02',	'2015-07-15 13:38:02'),
(20150703160719,	'2015-07-15 13:38:02',	'2015-07-15 13:38:02'),
(20150708084452,	'2015-07-15 13:38:02',	'2015-07-15 13:38:02'),
(20150708090254,	'2015-07-15 13:38:02',	'2015-07-15 13:38:02'),
(20150708134215,	'2015-07-15 13:38:02',	'2015-07-15 13:38:02'),
(20150710152835,	'2015-07-15 13:38:02',	'2015-07-15 13:38:02');

DROP TABLE IF EXISTS `queue`;
CREATE TABLE `queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `firstrex_id` int(11) DEFAULT NULL,
  `request_type` tinyint(4) DEFAULT NULL,
  `mls_number` varchar(50) DEFAULT NULL,
  `state` tinyint(4) DEFAULT NULL,
  `omit_realtor_info` enum('0','1') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `realtor_id` int(11) unsigned DEFAULT NULL,
  `listing_price` decimal(10,0) NOT NULL DEFAULT '0',
  `funded_percentage` decimal(4,2) NOT NULL DEFAULT '10.00',
  `maximum_loan` decimal(4,2) NOT NULL DEFAULT '80.00',
  `photo` text,
  `address` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `status_id` int(11) unsigned DEFAULT NULL,
  `status_other_text` text,
  `reason` text,
  `additional_info` text,
  PRIMARY KEY (`id`),
  KEY `queue_ibfk_1` (`user_id`),
  KEY `realtor_id` (`realtor_id`),
  KEY `queue_ibfk_3` (`status_id`),
  CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `queue_ibfk_2` FOREIGN KEY (`realtor_id`) REFERENCES `_queue_realtor` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `queue_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `queue` (`id`, `firstrex_id`, `request_type`, `mls_number`, `state`, `omit_realtor_info`, `realtor_id`, `listing_price`, `funded_percentage`, `maximum_loan`, `photo`, `address`, `created_at`, `updated_at`, `user_id`, `status_id`, `status_other_text`, `reason`, `additional_info`) VALUES
(1,	1828,	1,	'78',	2,	'0',	1,	778,	10.00,	80.00,	'https://s3-us-west-1.amazonaws.com/1rex/property/14316909175638.JPEG',	'23 Wandering Trail Dr, Brampton, ON L7A 1T5, Канада',	'2015-05-15 04:55:09',	'2015-05-15 04:55:09',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";s:22:\"23  Wandering Trail Dr\";s:4:\"city\";s:8:\"Brampton\";s:5:\"state\";s:2:\"ON\";s:3:\"zip\";s:7:\"L7A 1T5\";}'),
(2,	NULL,	1,	NULL,	4,	'0',	2,	2323,	10.00,	80.00,	'',	NULL,	'2015-05-15 06:42:58',	'2015-05-15 06:42:58',	99,	NULL,	NULL,	NULL,	'N;'),
(4,	1835,	1,	NULL,	3,	'0',	4,	323,	10.00,	80.00,	'https://s3-us-west-1.amazonaws.com/1rex/property/14317026948452.JPEG',	'22 Wandering Trail Dr, Brampton, ON L7A 1T5, Канада',	'2015-05-15 08:10:41',	'2015-05-15 08:10:41',	99,	NULL,	NULL,	'',	'a:4:{s:7:\"address\";s:22:\"22  Wandering Trail Dr\";s:4:\"city\";s:8:\"Brampton\";s:5:\"state\";s:2:\"ON\";s:3:\"zip\";s:7:\"L7A 1T5\";}'),
(5,	1840,	1,	NULL,	5,	'0',	28,	33,	11.00,	80.00,	'https://s3-us-west-1.amazonaws.com/1rex/property/143352260662371.JPEG',	'2200 Sacramento Street, San Francisco, CA 94115, США',	'2015-05-15 08:33:06',	'2015-05-15 08:33:06',	99,	NULL,	NULL,	'',	'N;'),
(6,	2090,	1,	NULL,	3,	'0',	5,	23234,	10.00,	80.00,	'https://s3-us-west-1.amazonaws.com/1rex/property/143175070480510.JPEG',	'2213 Wandering Ridge Dr, Chino Hills, CA 91709, США',	'2015-05-15 21:31:37',	'2015-05-15 21:31:37',	99,	NULL,	NULL,	'',	'a:4:{s:7:\"address\";s:24:\"2213  Wandering Ridge Dr\";s:4:\"city\";s:11:\"Chino Hills\";s:5:\"state\";s:2:\"CA\";s:3:\"zip\";s:5:\"91709\";}'),
(10,	NULL,	1,	NULL,	5,	'0',	9,	324234,	10.00,	80.00,	'',	NULL,	'2015-06-03 07:57:07',	'2015-06-03 07:57:07',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(11,	NULL,	1,	NULL,	5,	'0',	10,	123123,	10.00,	80.00,	'',	NULL,	'2015-06-03 07:57:24',	'2015-06-03 07:57:24',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(14,	NULL,	1,	NULL,	5,	'0',	13,	131231,	10.00,	80.00,	'',	NULL,	'2015-06-03 07:59:17',	'2015-06-03 07:59:17',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(15,	NULL,	1,	NULL,	5,	'0',	14,	312312,	10.00,	80.00,	'',	NULL,	'2015-06-03 08:00:24',	'2015-06-03 08:00:24',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(16,	NULL,	1,	NULL,	5,	'0',	15,	312312,	10.00,	80.00,	'',	NULL,	'2015-06-03 08:01:23',	'2015-06-03 08:01:23',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(17,	NULL,	1,	NULL,	5,	'0',	16,	323232,	10.00,	80.00,	'',	NULL,	'2015-06-03 08:01:56',	'2015-06-03 08:01:56',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(18,	NULL,	1,	NULL,	5,	'0',	17,	232,	10.00,	80.00,	'',	NULL,	'2015-06-03 08:14:43',	'2015-06-03 08:14:43',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(19,	NULL,	1,	NULL,	5,	'0',	18,	1231231,	10.00,	80.00,	'',	NULL,	'2015-06-03 08:20:34',	'2015-06-03 08:20:34',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(20,	2089,	1,	'232',	2,	'0',	25,	23232,	10.00,	80.00,	'https://s3-us-west-1.amazonaws.com/1rex/property/143351354246983.JPEG',	'1055 California Street, San Francisco, CA 94108, США',	'2015-06-03 08:36:27',	'2015-06-03 08:36:27',	99,	NULL,	NULL,	'',	'a:4:{s:7:\"address\";s:23:\"1055  California Street\";s:4:\"city\";s:13:\"San Francisco\";s:5:\"state\";s:2:\"CA\";s:3:\"zip\";s:5:\"94108\";}'),
(21,	NULL,	1,	NULL,	5,	'0',	19,	2312312,	10.00,	80.00,	'',	NULL,	'2015-06-03 08:36:53',	'2015-06-03 08:36:53',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(22,	NULL,	1,	NULL,	5,	'0',	20,	23232,	10.00,	80.00,	'',	NULL,	'2015-06-03 08:51:28',	'2015-06-03 08:51:28',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(23,	NULL,	1,	NULL,	5,	'0',	21,	3,	10.00,	80.00,	'',	NULL,	'2015-06-04 03:25:49',	'2015-06-04 03:25:49',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(25,	NULL,	1,	NULL,	5,	'0',	23,	9999999999,	10.00,	80.00,	'',	NULL,	'2015-06-05 04:50:21',	'2015-06-05 04:50:21',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(26,	NULL,	1,	NULL,	5,	'0',	24,	4444,	10.00,	80.00,	'',	NULL,	'2015-06-05 05:48:33',	'2015-06-05 05:48:33',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(27,	2096,	2,	NULL,	3,	'0',	NULL,	0,	10.00,	80.00,	NULL,	'1330 Eddy Street, San Francisco, CA 94115, USA',	'2015-06-05 07:05:07',	'2015-06-05 07:05:07',	99,	NULL,	NULL,	'',	'a:4:{s:7:\"address\";s:17:\"1330  Eddy Street\";s:4:\"city\";s:13:\"San Francisco\";s:5:\"state\";s:2:\"CA\";s:3:\"zip\";s:5:\"94115\";}'),
(29,	2101,	1,	'2324',	2,	'0',	29,	3223,	10.00,	80.00,	'https://s3-us-west-1.amazonaws.com/1rex/property/143352350952624.JPEG',	'1919 Octavia Street, San Francisco, CA 94109, USA',	'2015-06-05 09:48:35',	'2015-06-05 09:48:35',	99,	NULL,	NULL,	'',	'a:4:{s:7:\"address\";s:20:\"1919  Octavia Street\";s:4:\"city\";s:13:\"San Francisco\";s:5:\"state\";s:2:\"CA\";s:3:\"zip\";s:5:\"94109\";}'),
(31,	NULL,	1,	NULL,	5,	'0',	31,	0,	10.00,	80.00,	'https://s3-us-west-1.amazonaws.com/1rex/property/143358805454838.JPEG',	NULL,	'2015-06-06 03:54:08',	'2015-06-06 03:54:08',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(43,	NULL,	1,	NULL,	5,	'0',	43,	1,	10.00,	80.00,	'https://s3-us-west-1.amazonaws.com/1rex/property/143359324951055.JPEG',	NULL,	'2015-06-06 05:20:43',	'2015-06-06 05:20:43',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(44,	NULL,	1,	NULL,	5,	'0',	44,	0,	10.00,	80.00,	'',	NULL,	'2015-06-06 05:21:01',	'2015-06-06 05:21:01',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(45,	NULL,	1,	NULL,	5,	'0',	45,	0,	10.00,	80.00,	'',	NULL,	'2015-06-06 06:05:31',	'2015-06-06 06:05:31',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";N;s:4:\"city\";N;s:5:\"state\";N;s:3:\"zip\";N;}'),
(46,	2161,	2,	NULL,	2,	'0',	NULL,	0,	10.00,	80.00,	NULL,	'1501-1549 Steiner Street, San Francisco, CA 94115, USA',	'2015-06-08 09:01:01',	'2015-06-08 09:01:01',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";s:25:\"1501-1549  Steiner Street\";s:4:\"city\";s:13:\"San Francisco\";s:5:\"state\";s:2:\"CA\";s:3:\"zip\";s:5:\"94115\";}'),
(47,	2162,	2,	NULL,	2,	'0',	NULL,	0,	10.00,	80.00,	NULL,	'752 Divisadero Street, San Francisco, CA 94117, USA',	'2015-06-08 09:03:51',	'2015-06-08 09:03:51',	99,	NULL,	NULL,	NULL,	'a:4:{s:7:\"address\";s:22:\"752  Divisadero Street\";s:4:\"city\";s:13:\"San Francisco\";s:5:\"state\";s:2:\"CA\";s:3:\"zip\";s:5:\"94117\";}');

DROP TABLE IF EXISTS `queue_realtor`;
CREATE TABLE `queue_realtor` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  `realty_company_id` int(11) unsigned NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `bre_number` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `photo` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `first_last_name_unique` (`first_name`,`last_name`),
  KEY `first_name` (`first_name`),
  KEY `last_name` (`last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `queue_realtor` (`id`, `deleted`, `realty_company_id`, `first_name`, `last_name`, `bre_number`, `phone`, `email`, `photo`, `created_at`, `updated_at`) VALUES
(1,	'0',	36,	'Frank',	'Cheese',	'1934578',	'510.123.2458',	'joanna.umali@1rex.com',	'https://s3-us-west-1.amazonaws.com/1rex/realtor/14329273211593.JPEG',	NULL,	'2015-07-03 23:23:10');

DROP TABLE IF EXISTS `realtor`;
CREATE TABLE `realtor` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  `realty_company_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `bre_number` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `photo` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `first_last_name_unique` (`first_name`,`last_name`),
  KEY `first_name` (`first_name`),
  KEY `last_name` (`last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `realtor` (`id`, `deleted`, `realty_company_id`, `first_name`, `last_name`, `bre_number`, `phone`, `email`, `photo`, `created_at`, `updated_at`) VALUES
(1,	'0',	36,	'Frank',	'Cheese',	'1934578',	'510.123.2458',	'joanna.umali@1rex.com',	'https://s3-us-west-1.amazonaws.com/1rex/realtor/14329273211593.JPEG',	NULL,	'2015-07-15 13:38:02'),
(2,	'0',	36,	'Bonnie',	'Kyte',	'185739',	'415.234.5962',	'joanna.umali@1rex.com',	'https://s3-us-west-1.amazonaws.com/1rex/realtor/143267857582613.JPEG',	NULL,	'2015-07-15 13:38:02'),
(3,	'0',	36,	'John',	'Smith',	'18324838',	'510.990.2843',	'john.smith@realtor.com',	'https://s3-us-west-1.amazonaws.com/1rex/realtor/143265873063895.JPEG',	NULL,	'2015-07-15 13:38:02');

DROP TABLE IF EXISTS `realty_company`;
CREATE TABLE `realty_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `logo` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `realty_company` (`id`, `name`, `deleted`, `logo`, `created_at`, `updated_at`) VALUES
(1,	'Alain Pinel',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/14323945558968.JPEG',	'2015-06-02 15:28:45',	NULL),
(2,	'Insignia Mortgage',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143230346365938.JPEG',	'2015-06-02 15:28:45',	NULL),
(3,	'J. Rockcliff Mortgage',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143239038930129.JPEG',	'2015-06-02 15:28:45',	NULL),
(4,	'John L Scott Mortgage',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143239065380785.JPEG',	'2015-06-02 15:28:45',	NULL),
(5,	'Pacific Union',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143239407478962.JPEG',	'2015-06-02 15:28:45',	NULL),
(6,	'Realogics Sotheby',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143239363147637.JPEG',	'2015-06-02 15:28:45',	NULL),
(7,	'RealtyOne',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143239424956694.JPEG',	'2015-06-02 15:28:45',	NULL),
(8,	'Redfin',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143239441389146.JPEG',	'2015-06-02 15:28:45',	NULL),
(9,	'REMAX',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143239081382109.JPEG',	'2015-06-02 15:28:45',	NULL),
(10,	'REMAX Estate',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143239081382109.JPEG',	'2015-06-02 15:28:45',	NULL),
(11,	'Rockwell Realty',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143239098536592.JPEG',	'2015-06-02 15:28:45',	NULL),
(12,	'RSVP Real Estate',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(13,	'Skyline Properties',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/143239004842814.JPEG',	'2015-06-02 15:28:45',	NULL),
(14,	'Windermere Realty',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/14323901774058.JPEG',	'2015-06-02 15:28:45',	NULL),
(15,	'Windermere Realty Maple Valley',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/14323901774058.JPEG',	'2015-06-02 15:28:45',	NULL),
(16,	'Winkelmann',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(17,	'Better Properties Seattle King',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(18,	'Better Properties Solutions',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(19,	'First Team',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(20,	'Grubb Realty',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(21,	'Hallmark',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(22,	'Heritage Sotheby\'s',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(23,	'Indian Valley',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(24,	'McGuire Real Estate',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(25,	'NW Choice Realty',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(26,	'Pillar NorthWest Real Estate',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(27,	'Podley Properties',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(28,	'REMAX Integrity',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(29,	'Sotheby\'s Realty',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(30,	'Windermere Realty East',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(31,	'Keller Williams (white background)',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL),
(32,	'Keller Williams (no background)',	0,	'https://s3-us-west-1.amazonaws.com/1rex/realty.logo/no-logo.png',	'2015-06-02 15:28:45',	NULL);

DROP TABLE IF EXISTS `recovery_password`;
CREATE TABLE `recovery_password` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `date_expire` datetime NOT NULL,
  `signature` varchar(32) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `recovery_password_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sales_director`;
CREATE TABLE `sales_director` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sales_director` (`id`, `deleted`, `name`, `email`, `phone`, `created_at`, `updated_at`) VALUES
(1,	'0',	'Mike Lyon',	'mike.lyon@1rex.com',	'925-548-5157',	NULL,	NULL),
(2,	'0',	'Paul Careaga',	'paul.careaga@1rex.com',	'253-677-4470',	NULL,	NULL),
(3,	'0',	'Jim McGuire',	'jim.mcguire@1rex.com',	'310-909-6167',	NULL,	NULL);

DROP TABLE IF EXISTS `status`;
CREATE TABLE `status` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('approve','decline') DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `text` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `status` (`id`, `type`, `name`, `text`) VALUES
(1,	'approve',	'Approved',	'Your property has been approved for REX HomeBuyer.'),
(2,	'approve',	'Approved with Conditions',	'Your property is approved with additional conditions.  Please call your Sales Director for details.'),
(3,	'approve',	'Approved with 25% down',	'Your Property Approval request has been approved for REX HomeBuyer. Purchase requires a combined 25% down payment.'),
(4,	'approve',	'Approved with 25% down with conditions',	'Your Property Approval request has been approved for REX HomeBuyer. Purchase requires a combined 25% down payment. There are additional conditions.  Please call your Sales Director for details.'),
(5,	'decline',	'Typicality',	'Property is not typical for the area.  Attributes considered include but aren’t limited to home type, listing price, lot size, square footage etc.'),
(6,	'decline',	'Supply Abundance',	'Property is located in an area with a surplus of developable land.'),
(7,	'decline',	'Investment Guidelines',	'Property does not fall within our Investment Guidelines, which include but aren’t limited to: property type or condition, listing price or location.'),
(8,	'decline',	'New Construction in a 25% zone',	'Down payment funding is unavailable for newly constructed homes that fall within areas requiring a 25% down payment.'),
(9,	'decline',	'Rural',	'Property is in an area with a population density that falls below our guidelines.');

DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `hash` varchar(255) NOT NULL,
  `expiration_time` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tokens_hash` (`hash`),
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `fk_tokens_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tokens` (`id`, `user_id`, `hash`, `expiration_time`, `created_at`, `updated_at`) VALUES
(1,	99,	'$2y$10$K8nDKFEJMfAqT/h6tooZrOA5PPb2mOU0LLsElBJ6cnZp6GMZ/ZOjW',	'2015-05-20 04:53:25',	'2015-05-15 04:53:24',	'2015-05-15 04:53:24'),
(2,	99,	'$2y$10$A73fNkk6pc9JYEEiheOwMuXChNn7PpGMR/FKn8QjSdIl.zaoTRDuy',	'2015-05-21 00:07:29',	'2015-05-16 00:07:29',	'2015-05-16 00:07:29'),
(3,	99,	'$2y$10$QrXKpYseBFaZtaUuxhVJPekroOcIzpJpX.32c9MZXPDWrLE30TtwW',	'2015-06-07 02:52:13',	'2015-06-02 02:52:13',	'2015-06-02 02:52:13'),
(5,	99,	'$2y$10$AtqHHtKx3AHA/CTyaa/OT.0UluqREtjO7jTYQ6z4xJGcwX./erRZi',	'2015-06-08 05:30:23',	'2015-06-03 05:30:23',	'2015-06-03 05:30:23'),
(6,	99,	'$2y$10$HG1/NTrKcnY7W55FlU0sou/SgEUAzpSzDHTK/BxTHKr3XGZIvkMaa',	'2015-06-09 01:59:03',	'2015-06-04 01:59:03',	'2015-06-04 01:59:03'),
(7,	99,	'$2y$10$ycCtle3Vi.FqwuqPTB.4KO5WUp.w0YtLpvY20MenUe3SmzgrkCUTC',	'2015-06-10 05:12:34',	'2015-06-05 05:12:34',	'2015-06-05 05:12:34'),
(8,	99,	'$2y$10$qJHbv.PkfPnNt0pTpsOwU.tQX1TlY9EDfcuo.QPmBF5P936cAyfn6',	'2015-06-13 08:55:18',	'2015-06-08 08:55:18',	'2015-06-08 08:55:18'),
(9,	99,	'$2y$10$4w.Q7hXElxFy9fPGyvYTHuEV.yvEa0oc/zhI/Sc7r2O/oSgawVSlm',	'2015-06-13 08:56:27',	'2015-06-08 08:56:27',	'2015-06-08 08:56:27'),
(10,	99,	'$2y$10$pAuJC6kP2chN1gRH4yWU8.qcjHXr6CEey3ydDEFEEu0mKtfodQjxW',	'2015-06-13 08:56:40',	'2015-06-08 08:56:40',	'2015-06-08 08:56:40'),
(11,	99,	'$2y$10$lzh5TCBEbJwWzvNlQAUjFufvng3G3d.sXGLRHQ.hdKFzMSPnMI33a',	'2015-06-13 08:56:59',	'2015-06-08 08:56:59',	'2015-06-08 08:56:59'),
(12,	99,	'$2y$10$lUsBj8kr2FoNJfaHEVniJ.cL1fUOZM06t21kxJSmAqJaMqvtogdla',	'2015-06-13 08:57:17',	'2015-06-08 08:57:17',	'2015-06-08 08:57:17'),
(13,	99,	'$2y$10$1t7rGXDyoYMlVVrS.jjFy.REm7He4mKi8G1B9BmvS3y7Ti0mzrT/e',	'2015-06-13 08:57:48',	'2015-06-08 08:57:48',	'2015-06-08 08:57:48');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `deleted` enum('0','1') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `address_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `password` varchar(60) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `state` tinyint(4) DEFAULT NULL,
  `roles` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `mobile` varchar(100) DEFAULT NULL,
  `nmls` int(11) unsigned DEFAULT NULL,
  `sales_director` varchar(255) DEFAULT NULL,
  `sales_director_email` varchar(255) DEFAULT NULL,
  `sales_director_phone` varchar(100) DEFAULT NULL,
  `lender_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`),
  KEY `fk_lender_id` (`lender_id`),
  KEY `address_id` (`address_id`),
  CONSTRAINT `fk_lender_id` FOREIGN KEY (`lender_id`) REFERENCES `lender` (`id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `deleted`, `address_id`, `first_name`, `last_name`, `email`, `gender`, `password`, `salt`, `picture`, `state`, `roles`, `created_at`, `updated_at`, `title`, `phone`, `mobile`, `nmls`, `sales_director`, `sales_director_email`, `sales_director_phone`, `lender_id`) VALUES
(1,	'0',	NULL,	'Thomas',	'Duong',	'thomas.duong@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Branch Manager',	'(650) 600-5355',	'(650) 600-5355',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(2,	'0',	NULL,	'Sam',	'Spinella',	'samuel.spinella@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(650) 600-5346',	'(408) 393-9294',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(3,	'0',	NULL,	'Robert',	'Spinosa',	'rspinosa@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Agent',	'(415) 367-5959',	'',	22343,	'Mike Lyon',	NULL,	NULL,	1),
(4,	'0',	NULL,	'Graham',	'Tyler',	'grahamjtyler@gmail.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Mortgage Consultant',	'(408) 298-2591',	'(408) 688-7176',	0,	'Mike Lyon',	NULL,	NULL,	1),
(5,	'0',	NULL,	'Darlene',	'Keller',	'darlenekeller@gmail.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Real Estate Agent',	'(916) 879-8519 ext 9049',	'(916) 880-5212',	0,	'Mike Lyon',	NULL,	NULL,	1),
(6,	'0',	NULL,	'Paddie',	'Tecson',	'paddie.tecson@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(650) 438-4314',	'(925) 357-6284',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(7,	'0',	NULL,	'Chris',	'Quagliana',	'chris.quagliana@gmail.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Agent',	'(408) 219-2456',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(8,	'0',	NULL,	'Laura',	'St. Leger-Barter',	'lstleger@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Branch Manager',	'(707) 934-2314',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(9,	'0',	NULL,	'Jim',	'Nazer',	'jnazer@emeryfinancial.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 248-9200 ext 130',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(10,	'0',	NULL,	'John',	'Rodrigues',	'jrodrigues@emeryfinancial.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Vice President',	'(025) 248-9200 x106',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(11,	'0',	NULL,	'Robert',	'Scott',	'rob@emeryfinancial.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'',	'(925) 248-9200',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(12,	'0',	NULL,	'John',	'Wlcek',	'johnw@emeryfinancial.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Originator',	'',	'(925) 248-9200',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(13,	'0',	NULL,	'Robert',	'Salas',	'rsalas@emeryfinancial.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Vice President',	'',	'(925) 248-9200',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(14,	'0',	NULL,	'Brian',	'Paris',	'bparis@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 462-8050',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(15,	'0',	NULL,	'Jonathan',	'Pass',	'jpass@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 552-3874',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(16,	'0',	NULL,	'Carmelita',	'Perez',	'carmelita.perez@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 295-9345',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(17,	'0',	NULL,	'Steve',	'Peritore',	'speritore@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 524-8330',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(18,	'0',	NULL,	'Reina',	'Perkins',	'rperkins@msploan.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 224-9904',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(19,	'0',	NULL,	'Randy',	'Pickerell',	'rpickerell@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 648-7700',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(20,	'0',	NULL,	'Tai',	'Pilimai',	'tai.pilimai@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(916) 258-8844',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(21,	'0',	NULL,	'Joe',	'Polizzi',	'jpolizzi@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 552-3800',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(22,	'0',	NULL,	'Dominic',	'Pomilia',	'dpomilia@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 381-7015',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(23,	'0',	NULL,	'Susan',	'Pomilia',	'susanpomilia@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 381-7011',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(24,	'0',	NULL,	'Danny',	'Pouw',	'dpouw@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(831) 471-1977',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(25,	'0',	NULL,	'Rammil',	'Quizon',	'rquizon@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 849-1811',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(26,	'0',	NULL,	'Dave',	'Raffi',	'draffi@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 303-2931',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(27,	'0',	NULL,	'Debbie',	'Resch',	'debbie.resch@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(831) 262-6078',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(28,	'0',	NULL,	'Tyler',	'Richardson',	'trichardson@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 360-3621',	'',	0,	'Mike Lyon',	NULL,	NULL,	1),
(29,	'0',	NULL,	'Marcos',	'Rios',	'marcos.rios@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(51) 755-4055',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(30,	'0',	NULL,	'Tom',	'Roberts',	'troberts@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(510) 531-7005',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(31,	'0',	NULL,	'Kathleen',	'Rogers',	'krogers@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 953-0517',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(32,	'0',	NULL,	'Julie',	'Ross',	'jross@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 257-4900',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(33,	'0',	NULL,	'Brooks',	'Rumph',	'brooks.rumph@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 303-2599',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(34,	'0',	NULL,	'Joseph',	'Sanchez',	'joseph.sanchez@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(831) 320-2717',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(35,	'0',	NULL,	'Rainey',	'Sarmiento',	'rsarmiento@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 381-7043',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(36,	'0',	NULL,	'Robert',	'Schiff',	'rschiff@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 381-7042',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(37,	'0',	NULL,	'Bob',	'Schwab',	'bschwab@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 743-3512',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(38,	'0',	NULL,	'Chuck',	'Scoma',	'cscoma@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 627-7151',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(39,	'0',	NULL,	'Bill',	'Seaborg',	'bseaborg@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 381-7010',	'',	29887,	'Mike Lyon',	NULL,	NULL,	1),
(40,	'0',	NULL,	'Gina',	'Seaborg',	'gseaborg@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 381-5550 x241',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(41,	'0',	NULL,	'Oscar',	'Segura',	'osegura@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(559) 221-2365',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(42,	'0',	NULL,	'Erin',	'Selby',	'erin.selby@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 303-2592',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(43,	'0',	NULL,	'Lisa',	'Shaffer',	'lshaffer@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 402-4882',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(44,	'0',	NULL,	'Michael',	'Shirlock',	'mshirlock@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 306-7000',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(45,	'0',	NULL,	'Kane',	'Silverberg',	'ksilverberg@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(831) 475-5626',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(46,	'0',	NULL,	'Nicest',	'Sison',	'nsison@msploan.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 813-4510',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(47,	'0',	NULL,	'Leonard',	'Smith',	'leonard.smith@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Sales Manager',	'(925) 351-6629',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(48,	'0',	NULL,	'Gordon',	'Steele',	'gsteele@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 627-7109',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(49,	'0',	NULL,	'Roger',	'Smith',	'rsmith@lasallefinance.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(510) 339-4300 ext. 106',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(50,	'0',	NULL,	'Kenny',	'Stephens',	'kenny.stephen@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 412-3363',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(51,	'0',	NULL,	'Andrade',	'Sue',	'sandrade@lasallefinance.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(510) 339-4300 x103',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(52,	'0',	NULL,	'Stew',	'Sweet',	'ssweet@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 743-7500',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(53,	'0',	NULL,	'Michael',	'Taber',	'michael.taber@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 303-2909',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(54,	'0',	NULL,	'Aaron',	'Taylor',	'aaron.taylor@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 408-3790',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(55,	'0',	NULL,	'Jenna',	'Teyshak',	'jteyshak@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(209) 640-8000',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(56,	'0',	NULL,	'Craig',	'Thomason',	'cthomason@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 627-7155',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(57,	'0',	NULL,	'Peter',	'Thompson',	'pthompson@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 648-7700',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(58,	'0',	NULL,	'Dan',	'Toothman',	'dtoothman@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 407-9211',	'',	0,	'Mike Lyon',	NULL,	NULL,	1),
(59,	'0',	NULL,	'Mark',	'Treiber',	'mtreiber@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(530) 208-6704',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(60,	'0',	NULL,	'Linda',	'Tumas',	'ltumas@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(408) 583-3221',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(61,	'0',	NULL,	'Gary',	'Umholtz',	'gumholtz@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 343-9510',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(62,	'0',	NULL,	'Jennifer',	'Walker',	'jwalker@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(831) 471-1977',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(63,	'0',	NULL,	'Michael',	'Walker',	'mwalker@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 743-3506',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(64,	'0',	NULL,	'Paul',	'Watkins',	'pwatkins@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 849-1806',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(65,	'0',	NULL,	'Chris',	'Weber',	'cweber@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 306-7002',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(66,	'0',	NULL,	'Bart',	'Welles',	'bwelles@msploan.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 722-1592',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(67,	'0',	NULL,	'Tom',	'Vinson',	'tvinson@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 249-3812',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(68,	'0',	NULL,	'Sean',	'Wickersham',	'swickersham@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 287-1901',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(69,	'0',	NULL,	'Ken',	'Wirfel',	'kwirfel@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 295-9375',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(70,	'0',	NULL,	'Paul',	'Wirfel',	'pwirfel@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 295-9320',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(71,	'0',	NULL,	'Vince',	'Wirthman',	'vwirthman@msploan.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 394-4189',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(72,	'0',	NULL,	'Diane',	'Wood',	'dwood@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 819-5211',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(73,	'0',	NULL,	'Griffin',	'Zach',	'zgriffin@lasallefinancial.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(510) 339-4300 ext. 117',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(74,	'0',	NULL,	'Linda',	'Zaiss',	'linda@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 552-1185',	'',	8991,	'Mike Lyon',	NULL,	NULL,	1),
(75,	'0',	NULL,	'Mary',	'Kennaugh',	'mary.kennaugh@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(76,	'0',	NULL,	'Michael',	'Huppert',	'michael.huppert@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 575-1582',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(77,	'0',	NULL,	'Aaron',	'Meiliel',	'aaronm@goldenpacificbank.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(916) 778-3000',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(78,	'0',	NULL,	'Beckie',	'Johnstone',	'bjohnstone@goldenpacificbank.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Mortgage Consultant',	'(916) 835-5672',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(79,	'0',	NULL,	'Polly',	'Cracchiolo',	'pcracchiolo@goldenpacificbank.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Account Executive',	'(916) 601-8738',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(80,	'0',	NULL,	'Terry',	'Conner',	'terry.conner@homestreet.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(916) 367-3881',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(81,	'0',	NULL,	'Todd',	'Vinther',	'tvinther@goldenpacificbank.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Sales Manager',	'(916) 414-0650',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(82,	'0',	NULL,	'Frank',	'Aceves',	'faceves@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(559) 221-2357',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(83,	'0',	NULL,	'Josette',	'Alexander',	'josette.alexander@homestreet.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(949) 849-1824',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(84,	'0',	NULL,	'Celeste',	'Anderson',	'canderson@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 295-9339',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(85,	'0',	NULL,	'Tom',	'Andrews',	'tom.andrews@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 303-2907',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(86,	'0',	NULL,	'John',	'Assily',	'jassily@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 552-3885',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(87,	'0',	NULL,	'Peter',	'Barnes',	'peter.barnes@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(510) 647-5337',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(88,	'0',	NULL,	'Peter',	'Barnett',	'pbarnett@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(415) 381-7054',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(89,	'0',	NULL,	'Tony',	'Bell',	'tony.bell@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(831) 676-8775',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(90,	'0',	NULL,	'Kat',	'Rider',	'krider@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Consultant',	'(925) 743-3515',	'',	22928,	'Mike Lyon',	NULL,	NULL,	1),
(91,	'0',	NULL,	'Michael',	'Bertoli',	'michael.bertoli@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(916) 899-5140',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(92,	'0',	NULL,	'James',	'Bingham',	'jbingham@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(559) 273-0376',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(93,	'0',	NULL,	'Lara',	'Blake',	'lblake@lasallefinancial.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(510) 339-4300 x213',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(94,	'0',	NULL,	'Gabe',	'Bodner',	'gbodner@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(408) 426-4416',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(95,	'0',	NULL,	'Audrey',	'Boissonou',	'aboissonou@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(925) 263-3109',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(96,	'0',	NULL,	'Mark',	'Bootman',	'mark.bootman@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(408) 202-5656',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(97,	'0',	NULL,	'Steve',	'Bringuel',	'steve.bringuel@banchomeloans.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(650) 394-6308',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(98,	'0',	NULL,	'Mary',	'Brusco',	'mbrusco@rpm-mtg.com',	NULL,	'123456',	'6f8494e32b77d03dd13ae2fc3b4fb51f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	NULL,	NULL,	'Loan Officer',	'(707) 477-2947',	'',	32767,	'Mike Lyon',	NULL,	NULL,	1),
(99,	'0',	1,	'AdminFirst',	'AdminLast',	'admin@1rex.com',	NULL,	'13',	'92ec4782440e452dfa17c76cca3af920',	NULL,	1,	'a:1:{i:0;s:10:\"ROLE_ADMIN\";}',	NULL,	NULL,	NULL,	NULL,	NULL,	2234,	NULL,	NULL,	NULL,	1),
(100,	'0',	2,	'sss',	'dddd',	's.samoilenko23@gmail.com',	NULL,	'304d31d3',	'7742d6f29357514b8d7daeedfd40850f',	NULL,	1,	'a:1:{i:0;s:9:\"ROLE_USER\";}',	'2015-06-05 00:55:38',	'2015-06-05 00:55:38',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1);

-- 2015-07-15 13:38:46