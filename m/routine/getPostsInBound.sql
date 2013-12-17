-- --------------------------------------------------------------------------------
-- This is the wrapper for getPostsInBoundSimple
-- handles the case where the boundries may need to be separated in two regions dividing the -180/180 longitude line
-- --------------------------------------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS `p13`.`getPostsInBound`;

CREATE PROCEDURE `p13`.`getPostsInBound` (IN swlat DECIMAL(10,6), IN swlng DECIMAL(10,6), IN nelat DECIMAL(10,6), IN nelng DECIMAL(10,6), IN lim SMALLINT, IN afterId INT(10), IN tags VARCHAR(1024))
BEGIN
    
    CREATE TEMPORARY TABLE IF NOT EXISTS `PostsInBound` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `userId` int(10) unsigned NOT NULL,
        `username` varchar(128) NOT NULL,
        `parentId` int(10) unsigned DEFAULT NULL,
        `title` varchar(512) NOT NULL,
        `content` text,
        `visibility` tinyint(4) DEFAULT '0',
        `location` varchar(128) DEFAULT NULL,
        `latitude` decimal(10,6) DEFAULT NULL,
        `longitude` decimal(10,6) DEFAULT NULL,
        `vote` int(11) NOT NULL DEFAULT '1',
        `influence` int(11) NOT NULL DEFAULT '0',
        `comment` int(11) NOT NULL DEFAULT '0',
        `created` datetime NOT NULL,
        `modified` datetime NOT NULL,
        `tags` varchar(256) NOT NULL,
        PRIMARY KEY (`id`));

    DELETE FROM PostsInBound;
    
    IF swlng > nelng THEN
        CALL getPostsInBoundSimple (swlat, swlng, nelat, 180, lim, afterId, tags);
        CALL getPostsInBoundSimple (swlat, -180, nelat, nelng, lim, afterId, tags);
    ELSE
        CALL getPostsInBoundSimple (swlat, swlng, nelat, nelng, lim, afterId, tags);
    END IF;
    
    SELECT * FROM PostsInBound
    ORDER BY influence DESC, id DESC
    LIMIT lim;
END