SQL :

CREATE DATABASE `todo` /*!40100 DEFAULT CHARACTER SET utf8 */;
CREATE TABLE `task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=233 DEFAULT CHARSET=utf8;
CREATE TABLE `calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idtask` int(11) NOT NULL,
  `dateStart` datetime NOT NULL,
  `dateEnd` datetime NOT NULL,
  `reiterate` int(11) DEFAULT '0' COMMENT '0 : une fois, 1 : tous les jours, 2 : toutes les semaines, 3 : Tous les mois, 4 : Tous les ans',
  `interspace` int(11) DEFAULT '0' COMMENT '0 : toujours, 1 : jusqu''à une date, 2 : nombre de fois',
  `reiterateEnd` int(11) DEFAULT '0' COMMENT 'Tous les x jours/semaines/mois/années',
  `untilDate` datetime DEFAULT NULL COMMENT 'répéter jusqu''à cette date',
  `untilNumber` int(11) DEFAULT '0' COMMENT 'x répétitions',
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;
CREATE TABLE `performe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcalendar` int(11) NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
