/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `adminnote` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `groupView` tinyint(3) unsigned NOT NULL DEFAULT '255',
  `groupEdit` tinyint(3) unsigned NOT NULL DEFAULT '255',
  `title` varchar(300) NOT NULL,
  `html` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `articlecategory_en` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` smallint(5) unsigned NOT NULL,
  `title` varchar(35) NOT NULL,
  `metaTitle` varchar(50) DEFAULT NULL,
  `metaKeyword` varchar(255) DEFAULT NULL,
  `metaDescription` varchar(255) DEFAULT NULL,
  `alias` char(35) NOT NULL,
  `text1` mediumtext,
  `text2` mediumtext,
  `onPage` tinyint(3) unsigned NOT NULL DEFAULT '20',
  PRIMARY KEY (`id`),
  KEY `parentId` (`parentId`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `articlecategory_ru` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` smallint(5) unsigned NOT NULL,
  `title` varchar(35) NOT NULL,
  `metaTitle` varchar(50) DEFAULT NULL,
  `metaKeyword` varchar(255) DEFAULT NULL,
  `metaDescription` varchar(255) DEFAULT NULL,
  `alias` char(35) NOT NULL,
  `text1` mediumtext,
  `text2` mediumtext,
  `onPage` tinyint(3) unsigned NOT NULL DEFAULT '20',
  PRIMARY KEY (`id`),
  KEY `parentId` (`parentId`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `article_en` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `alias` char(56) NOT NULL,
  `title` char(150) NOT NULL,
  `text1` mediumtext,
  `text2` longtext NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `metaTitle` varchar(255) DEFAULT NULL,
  `metaKeyword` varchar(255) DEFAULT NULL,
  `metaDescription` varchar(255) DEFAULT NULL,
  `date` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoryId` (`categoryId`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `article_ru` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `alias` char(60) NOT NULL,
  `title` char(150) NOT NULL,
  `text1` mediumtext,
  `text2` longtext NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `metaTitle` varchar(255) DEFAULT NULL,
  `metaKeyword` varchar(255) DEFAULT NULL,
  `metaDescription` varchar(255) DEFAULT NULL,
  `date` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoryId` (`categoryId`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `catalog_1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` char(40) NOT NULL,
  `title` varchar(150) NOT NULL,
  `metaTitle` varchar(300) NOT NULL,
  `metaKeyword` varchar(300) NOT NULL,
  `metaDescription` varchar(300) NOT NULL,
  `genry` varchar(300) NOT NULL DEFAULT '',
  `year` mediumint(8) unsigned DEFAULT NULL,
  `director` varchar(300) NOT NULL DEFAULT '',
  `country` varchar(300) NOT NULL DEFAULT '',
  `actor` varchar(300) NOT NULL DEFAULT '',
  `description1` mediumtext,
  `description2` mediumtext,
  `mainPicture` char(50) DEFAULT NULL,
  `translate` char(50) DEFAULT NULL,
  `picture` varchar(300) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chatban` (
  `ip` char(26) NOT NULL,
  `date` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` smallint(5) unsigned NOT NULL,
  `userId` int(10) unsigned DEFAULT NULL,
  `date` int(10) unsigned NOT NULL,
  `name` char(25) NOT NULL,
  `text` varchar(500) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupId` (`groupId`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `commentgroup` (
  `link` char(40) NOT NULL,
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `link` (`link`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `demotivator` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(40) NOT NULL,
  `image` char(15) NOT NULL,
  `author` char(30) NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL,
  `metaKeyword` varchar(300) DEFAULT NULL,
  `metaDescription` varchar(300) DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `faq` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(25) NOT NULL,
  `question` varchar(400) NOT NULL,
  `answer` text,
  `email` char(30) DEFAULT NULL,
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumcategory` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(200) NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `metaTitle` varchar(500) DEFAULT NULL,
  `metaKeyword` varchar(500) DEFAULT NULL,
  `metaDescription` varchar(800) DEFAULT NULL,
  `newTopic` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `newPost` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumpost` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topicId` mediumint(8) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumtopic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` smallint(5) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `title` char(200) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `lastDate` int(10) unsigned NOT NULL DEFAULT '0',
  `postCount` smallint(5) unsigned NOT NULL DEFAULT '0',
  `message` text,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumuser` (
  `id` int(10) unsigned NOT NULL,
  `login` char(25) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `ip` char(15) DEFAULT NULL,
  `avatar` char(11) DEFAULT NULL,
  `postCount` smallint(5) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `frmfield` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `formId` mediumint(8) unsigned NOT NULL,
  `title_ru` char(25) NOT NULL,
  `htmlType` enum('text','radio','select','checkbox','textarea','email','file','captcha') NOT NULL,
  `data_ru` text,
  `defaultValue` varchar(100) NOT NULL DEFAULT '0',
  `required` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sort` tinyint(3) NOT NULL DEFAULT '0',
  `title_en` char(25) NOT NULL,
  `data_en` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `frmform` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title_ru` char(35) NOT NULL,
  `email` varchar(30) NOT NULL,
  `subject_ru` varchar(150) NOT NULL,
  `successMessage_ru` text NOT NULL,
  `redirect` varchar(255) DEFAULT NULL,
  `formView` varchar(15) DEFAULT NULL,
  `script` varchar(15) DEFAULT NULL,
  `title_en` char(35) NOT NULL,
  `subject_en` varchar(150) NOT NULL,
  `successMessage_en` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1021 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `menu` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `menuitem` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `menuId` smallint(5) unsigned NOT NULL,
  `typeId` smallint(5) unsigned NOT NULL,
  `link` varchar(255) NOT NULL,
  `title_ru` char(30) NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `title_en` char(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `menuId` (`menuId`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `menutype` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(25) NOT NULL,
  `controller` char(20) NOT NULL,
  `action` char(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=520 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `modified` (
  `link` char(120) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`link`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `oauth` (
  `id` bigint(15) unsigned NOT NULL,
  `social` enum('vk','facebook') NOT NULL,
  `userId` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned DEFAULT NULL,
  `method` char(25) DEFAULT NULL,
  `status` enum('request','success','wrong','cancel') NOT NULL DEFAULT 'request',
  `date` int(10) unsigned NOT NULL,
  `amount` float unsigned NOT NULL DEFAULT '0',
  `data` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `section` (
  `name` char(20) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `widgetId` smallint(5) unsigned NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shpbrand` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(50) NOT NULL,
  `image` char(11) NOT NULL DEFAULT '',
  `text1` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shpcategory` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `alias` char(50) NOT NULL,
  `title` char(50) NOT NULL,
  `text1` mediumtext,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image` char(11) DEFAULT NULL,
  `feature` varchar(1000) NOT NULL DEFAULT '',
  `metaTitle` varchar(500) NOT NULL DEFAULT '',
  `metaKeyword` varchar(500) NOT NULL DEFAULT '',
  `metaDescription` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shpfeature` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('text','checkbox','select') NOT NULL,
  `groupId` smallint(5) unsigned NOT NULL,
  `title` char(100) NOT NULL,
  `unit` char(12) NOT NULL DEFAULT '',
  `variant` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `data` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shpfeaturegroup` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shpproduct` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` smallint(5) unsigned NOT NULL,
  `brandId` mediumint(8) unsigned DEFAULT NULL,
  `alias` char(25) NOT NULL,
  `title` char(60) NOT NULL,
  `text1` text,
  `text2` mediumtext,
  `price` float unsigned NOT NULL DEFAULT '0',
  `mainImage` char(13) NOT NULL DEFAULT '',
  `image` varchar(255) DEFAULT NULL,
  `metaTitle` varchar(300) DEFAULT '',
  `metaKeyword` varchar(300) DEFAULT '',
  `metaDescription` varchar(300) DEFAULT '',
  `variant` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `quantity` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shpproductfeature` (
  `productId` int(10) unsigned NOT NULL,
  `featureId` smallint(5) unsigned NOT NULL,
  `value` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shpproductgroup` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shpproductgroupitem` (
  `groupId` smallint(5) unsigned NOT NULL,
  `productId` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shpvariant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productId` int(10) unsigned NOT NULL,
  `title` char(60) NOT NULL,
  `feature` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `login` char(25) NOT NULL,
  `password` char(32) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `email` char(30) DEFAULT NULL,
  `code` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `usergroup` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=256 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `usermessage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user1Id` int(10) unsigned NOT NULL,
  `user1Login` char(25) NOT NULL,
  `user2Id` int(10) unsigned NOT NULL,
  `user2Login` char(25) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `isNew` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `userright` (
  `module` char(30) NOT NULL,
  `groupId` varchar(255) DEFAULT NULL,
  `description` char(50) NOT NULL,
  `picture` char(11) DEFAULT NULL,
  PRIMARY KEY (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(300) NOT NULL,
  `answer` text NOT NULL,
  `result` text NOT NULL,
  `ip` char(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `widget` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` tinyint(3) unsigned DEFAULT NULL,
  `name` char(25) DEFAULT NULL,
  `data` varchar(2000) DEFAULT NULL,
  `cache` int(10) unsigned NOT NULL DEFAULT '0',
  `title_ru` char(35) DEFAULT NULL,
  `publicTitle` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `section` char(20) NOT NULL,
  `title_en` char(35) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `section` (`section`)
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `widgettype` (
  `name` char(20) NOT NULL,
  `title` char(35) NOT NULL,
  `controller` char(20) NOT NULL,
  `action` char(20) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
