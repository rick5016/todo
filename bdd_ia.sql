CREATE TABLE `ia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mot` varchar(45) NOT NULL,
  `classe` varchar(45) DEFAULT NULL,
  `idb` int(11) DEFAULT NULL,
  `com` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mot_UNIQUE` (`mot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `determinant` (
  `id` int(11) NOT NULL,
  `mot` varchar(45) NOT NULL,
  `nombre` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `todo`.`verbe` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `mot` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`));

  CREATE TABLE `ia_todo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mot` varchar(45) NOT NULL,
  `action` varchar(45) DEFAULT NULL COMMENT 'ajouter\nsupprimer\nprojet\ntache\ntitre',
  `value` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  
  
INSERT INTO `ia` (`mot`, `classe`) VALUES ('le', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('la', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('les', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('l', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('un', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('une', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('des', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('du', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('au', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('quelques', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('tout', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('un', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('deux', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('trois', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('premier', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('dixième', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('quel', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('quelle', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('mon', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('ton', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('ses', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('leur', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('ce', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('cette', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('cet', 'determinant');
INSERT INTO `ia` (`mot`, `classe`) VALUES ('ces', 'determinant');