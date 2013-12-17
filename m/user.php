<?php
class UserModel extends Model {
    public function getRandomUser () {
        $q = 'SELECT * FROM user ORDER BY RAND() LIMIT 0,1;';
        $r = $this->fetchWithKey($q, FALSE);
        return $r[0];
    }
    
    public function updateUsername($id, $params) {
        $sql = "UPDATE user SET usernameChange=usernameChange+1, ".implode(',', $this->getAssignmentArr($params))." WHERE id=$id";
        $this->mysqli->query($sql);
        $this->setSqlError($sql);
    }
}