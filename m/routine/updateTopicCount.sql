-- --------------------------------------------------------------------------------
-- Routine DDL
-- Note: comments before and after the routine body will not be stored by the server
-- --------------------------------------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS `broadcaster`.`updateTopicCount`;

CREATE DEFINER=`root`@`24.6.160.109` PROCEDURE `updateTopicCount`()
BEGIN
    UPDATE tag t
    INNER JOIN (
        SELECT  tagId, count(*) as total
        FROM    post_tag
        GROUP   BY tagId
    ) pt ON t.id = pt.tagId
    SET t.count = pt.total;
END