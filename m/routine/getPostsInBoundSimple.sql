-- --------------------------------------------------------------------------------
-- This is the core routin to get posts
-- assuming the boundries form a valid square
-- afterID = 0 is default
-- --------------------------------------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS `p13`.`getPostsInBoundSimple`;

CREATE PROCEDURE `p13`.`getPostsInBoundSimple` (IN swlat DECIMAL(10,6), IN swlng DECIMAL(10,6), IN nelat DECIMAL(10,6), IN nelng DECIMAL(10,6), IN lim SMALLINT, IN afterId INT(10), IN tags VARCHAR(1024))
BEGIN
    DECLARE afterInf SMALLINT DEFAULT 100;
    DECLARE maxInfluence SMALLINT DEFAULT 100;

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

    SELECT COALESCE(influence, maxInfluence) INTO afterInf
    FROM post_active
    WHERE id = afterId;

    IF tags = '[[RESERVED_TAG_EVERYTHING]]' THEN
        INSERT INTO PostsInBound (
            SELECT p.*
            FROM post_active p
            WHERE p.parentId IS NULL AND
                p.latitude >= swlat AND p.latitude < nelat AND
                p.longitude >= swlng AND p.longitude < nelng AND
                (p.influence < afterInf OR (p.influence = afterInf AND p.id < afterId))
            LIMIT lim);
    ELSE
        INSERT INTO PostsInBound (
            SELECT p.*
            FROM post_active p
            WHERE p.parentId IS NULL AND
                p.latitude >= swlat AND p.latitude < nelat AND
                p.longitude >= swlng AND p.longitude < nelng AND
                (p.influence < afterInf OR (p.influence = afterInf AND p.id < afterId)) AND
                EXISTS ( SELECT 1 FROM post_tag pt WHERE p.id = pt.postId AND EXISTS (
                    SELECT 1 FROM tag t WHERE t.id = pt.tagId AND FIND_IN_SET(t.name, tags) > 0 ))
            LIMIT lim
        );
    END IF;
END