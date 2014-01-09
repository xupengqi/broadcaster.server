<?php
class MVC {
    protected $context;
    
    public $DB_KEY_NULL = 'DB_KEY_NULL';
    public $DB_KEY_NOW = 'DB_KEY_NOW';
    
    public function __construct($c) {
        $this->context = $c;
    }
}