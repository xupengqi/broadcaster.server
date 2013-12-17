<?php
class MVC {
    protected $context;
    
    public $DB_KEY_NULL = 'DB_KEY_NULL';
    public $DB_KEY_NOW = 'DB_KEY_NOW';

    public $ERR_ID_WRONG_PASS = 1;
    public $ERR_ID_USER_NOT_FOUND = 2;
    public $ERR_ID_SQL_ERROR = 3;
    public $ERR_ID_REQUIRE_LOGIN = 4;
    public $ERR_ID_MISSING_PARAMETER = 5;
    public $ERR_ID_RESOURCE_NOT_FOUND = 6;
    public $ERR_ID_USER_ALREADY_EXIST = 7;
    
    public function __construct($c) {
        $this->context = $c;
    }
}