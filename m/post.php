<?php
class PostModel extends Model {
    public function getRandomParentPost () {
        $q = 'SELECT * FROM post WHERE parentId IS NULL ORDER BY RAND() LIMIT 0,1;';
        $r = $this->fetchWithKey($q, FALSE);
        return $r[0];
    }
    
    public function incComment ($id, $operator='+') {
        $sql = "UPDATE post SET comment=comment{$operator}1 WHERE id=".$id;
        $this->mysqli->query($sql);
        $this->setSqlError($sql);
    }
}