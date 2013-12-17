<?php
class Context {
    public $config;
    public $controller;
    public $controllerName;
    public $controllerClass;
    public $action;
    public $request = array();
    public $messages = array();
    public $helpers = array();
    public $models = array();
    public $layout;
    public $views;
    public $user;

    public function __construct() {
    }

    public function isDebugging() {
        return $this->config->config['debug'];
    }

    public function setConfig($cfg) {
        $this->config = $cfg;

        if($cfg->config['debug']) {
            ini_set('display_errors', 1);
            ini_set('html_errors', 1);
        }
    }
    public function getConfig() {
        return $this->config->config;
    }

    public function setController($ctrl) {
        require_once "/c/$ctrl.php";
        $this->controllerName = $ctrl;
        $this->controllerClass = $ctrl.'Controller';
        $this->controller  = new $this->controllerClass($this);
    }
    public function getController() {
        return $this->controller;
    }
    public function getControllerClass() {
        return $this->controllerClass;
    }
    public function getControllerName() {
        return $this->controllerName;
    }

    public function setAction($a) {
        $this->action = $a;
    }

    public function getAction() {
        return $this->action;
    }

    public function setParam($key, $val) {
        $this->request[$key] = $val;

        if($key == 'token') {
            $this->loadModels(array('user'));
            $this->loadHelpers(array('response'));
            $this->user = $this->models['user']->getSingle(array('token'=>$val), false);
        }
    }

    public function getParam($key) {
        return $this->request[$key];
    }

    public function getParams() {
        return $this->request;
    }

    public function setMessage($type, $message) {
        if(!isset($this->messages[$type]))
            $this->messages[$type] = array();
        $this->messages[$type][] = $message;
    }
    public function getMessages() {
        return $this->messages;
    }

    public function loadHelpers($helpers) {
        require_once '/lib/helper.php';
        foreach($helpers as $helper) {
            if(!isset($this->helpers[$helper])) {
                require_once "/lib/helper/$helper.php";
                $helperClass = $helper.'Helper';
                $this->helpers[$helper] = new $helperClass($this);
            }
        }
    }

    public function loadModels($models) {
        foreach($models as $model) {
            if(!isset($this->models[$model])) {
                $modelName = 'Model';
                if(file_exists(getcwd()."\\m\\{$model}.php")) {
                    $modelName = "{$model}Model";
                    require_once "/m/$model.php";
                }
                $this->models[$model] = new $modelName($this, $model);
            }
        }
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }
    public function setView($key, $val) {
        $this->views[$key] = $val;
    }
    public function setViews($views) {
        $this->views = $views;
    }
}