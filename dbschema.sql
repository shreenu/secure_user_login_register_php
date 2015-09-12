CREATE DATABASE `secure_app`;

CREATE USER 'sec_user'@'localhost' IDENTIFIED BY 'eKcGZr59zAa2BEWU';

GRANT SELECT, INSERT, UPDATE ON `secure_app`.`login_attempts` TO 'sec_user'@'localhost';

GRANT SELECT, INSERT, UPDATE ON `secure_app`.`members` TO 'sec_user'@'localhost';

CREATE TABLE `secure_app`.`members` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(30) NOT NULL,
    `email` VARCHAR(50) NOT NULL,
    `password` CHAR(128) NOT NULL,
    `salt` CHAR(128) NOT NULL 
) ENGINE = InnoDB;

CREATE TABLE `secure_app`.`login_attempts` (
    `user_id` INT(11) NOT NULL,
    `time` VARCHAR(30) NOT NULL
) ENGINE=InnoDB;

INSERT INTO `secure_app`.`members` VALUES(1, 'test_user', 'test@example.com',
'00807432eae173f652f2064bdca1b61b290b52d40e429a7d295d76a71084aa96c0233b82f1feac45529e0726559645acaed6f3ae58a286b9f075916ebf66cacc',
'f9aab579fc1b41ed0c44fe4ecdbfcdb4cb99b9023abb241a6db833288f4eea3c02f76e0d35204a8695077dcf81932aa59006423976224be0390395bae152d4ef');