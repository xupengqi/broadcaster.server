<?php
class Model extends MVC {
    protected $table;
    protected $mysqli;

    public function __construct($c, $t) {
        $this->context = $c;
        $this->table = $t;

        $config = $this->context->getConfig();
        $this->mysqli = new mysqli($config['db']['host'], $config['db']['user'], $config['db']['pass'], $config['db']['name']);
        if ($this->mysqli->connect_error) {
            $this->context->loadHelpers(array('response'));
            $this->context->helpers['response']->setError($this->ERR_ID_SQL_ERROR, $this->mysqli->connect_error);
            $this->context->helpers['response']->flush();
            exit();
        }
    }

    public function real_escape_string($var) {
        return $this->mysqli->real_escape_string($var);
    }

    public function create($params) {
        $insert = $this->getAssignmentArr($params);
        $sql = "INSERT INTO {$this->table} SET created = NOW(), modified = NOW(), ".implode(',', $insert);
        $this->mysqli->query($sql);

        if(!$this->setSqlError($sql)) {
            return $this->mysqli->insert_id;
        }
        return 0;
    }

    public function createPossibleDupeUser($params, $tries = 10) {
        $insert = $this->getAssignmentArr($params);
        $sql = "INSERT INTO {$this->table} SET created = NOW(), modified = NOW(), ".implode(',', $insert);
        $this->mysqli->query($sql);

        // 1062 = duplicate entry
        $originalUsername = $params['username'];
        $i = 1;
        while ($this->mysqli->errno == 1062 && $tries > 0) {
            $params['username'] = $originalUsername.$i;
            $i++;
            $insert = $this->getAssignmentArr($params);
            $sql = "INSERT INTO {$this->table} SET created = NOW(), modified = NOW(), ".implode(',', $insert);
            $this->mysqli->query($sql);
        }
        
        if(!$this->setSqlError($sql)) {
            $params['id'] = $this->mysqli->insert_id;
            return $params;
        }
        return 0;
    }

    public function update($id, $params) {
        $where = '';
        if (is_array($id)) {
            $where = implode(' AND ', $this->getAssignmentArr($id));
        }
        else {
            $where = "id=".$this->mysqli->real_escape_string($id);
        }

        $update = $this->getAssignmentArr($params);
        $sql = "UPDATE {$this->table} SET modified = NOW(), ".implode(',', $update)." WHERE $where";
        $this->mysqli->query($sql);
        $this->setSqlError($sql);
    }

    public function delete($where) {
        $whereClause = array();
        foreach($where as $key=>$val) {
            $eKey = $this->mysqli->real_escape_string($key);
            $eVal = $this->escape($this->mysqli->real_escape_string($val));
            $whereClause[] = "$eKey=$eVal";
        }
        $sql = "DELETE FROM {$this->table} WHERE ".implode(' AND ', $whereClause);

        $this->mysqli->query($sql);
        $this->setSqlError($sql);
    }

    public function getMulti($cond = array(), $key = false, $extra = false) {
        $q = "SELECT * FROM %s %s";
        $where = '';

        if(count($cond) > 0) {
            $c = array();
            foreach($cond as $field =>$value) {
                $op = '=';
                if(isset($cond['operators']) && isset($cond['operators'][$field])) {
                    $op = $cond['operators'][$field];
                }
                if($field != 'operators') {
                    if($value == 'NULL' || $op == 'IN') {
                        $c[] = "$field $op $value";
                    }
                    else {
                        $c[] = "$field $op '$value'";
                    }
                }
            }
            $where = ' WHERE '.implode(' AND ', $c);
        }

        return $this->fetchWithKey(sprintf($q, $this->table, $where.' '.$extra), $key);
    }

    public function getSingle($cond = array()) {
        $data = $this->getMulti($cond);
        if(count($data) > 0) {
            return $data[0];
        }
        else {
            //$this->context->loadHelpers(array('response'));
            //$this->context->helpers['response']->setError($this->ERR_ID_SQL_ERROR,
            //    "Unable to find matching record in {$this->table} table with condition: ".print_r($cond,true));
            return array();
        }
    }

    public function routine($name, $vars = array(), $key = false) {
        $params = '';
        if(count($vars) > 0) {
            $params = "'".implode("','", $vars)."'";
        }
        $q = "CALL $name($params)";
        return $this->fetchWithKey($q, $key);
    }

    protected function fetchWithKey($query, $key = false) {
        $this->mysqli->multi_query($query);
        $list = array();
        do {
            if($result = $this->mysqli->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    if(isset($row['content']) && ($this->table == 'post' || $this->table == 'post_active')) {
                        $row['content'] = json_decode($row['content']);
                    }
                    if($key)
                        $list[$row[$key]] = $row;
                    else
                        $list[] = $row;
                }
                $result->free();
            }
            if ($this->mysqli->more_results()) {
                $this->mysqli->next_result();
            }
            else {
                break;
            }
        } while (true);

        $this->setSqlError($query);

        return $list;
    }

    protected function escape($val) {
        switch($val) {
            case $this->DB_KEY_NOW:
                return 'NOW()';
            case $this->DB_KEY_NULL:
                return 'NULL';
            default:
                return "'$val'";
        }
    }

    protected function getAssignmentArr($params) {
        $assignment = array();
        foreach($params as $col=>$val) {
            $eCol = $this->mysqli->real_escape_string($col);
            $eVal = $this->escape($this->mysqli->real_escape_string($val));
            $assignment[] = "$eCol=$eVal";
        }
        return $assignment;
    }

    protected function setSqlError($query) {
        $this->context->loadHelpers(array('response'));
        error_log($query);

        if(!empty($this->mysqli->error)) {
            if($this->context->isDebugging()) {
                $this->context->helpers['response']->setError($this->ERR_ID_SQL_ERROR, "[{$this->mysqli->errno}] {$this->mysqli->error}\n$query", $this->mysqli->errno);
            }
            else {
                $this->context->helpers['response']->setError($this->ERR_ID_SQL_ERROR, "[{$this->mysqli->errno}] {$this->mysqli->error}", $this->mysqli->errno);
            }
            error_log("[{$this->mysqli->errno}] {$this->mysqli->error}");

            return true;
        }

        return false;
    }
}
