<?php
class App {
    private $context;

    public function Dispatch() {
        session_start();
        date_default_timezone_set('America/Los_Angeles');

        require_once '/lib/config.php';
        require_once '/lib/context.php';
        require_once '/lib/mvc.php';
        require_once '/lib/model.php';
        require_once '/lib/view.php';
        require_once '/lib/controller.php';
        require_once '/lib/restController.php';

        $cfg = new Config();
        $this->context = new Context();
        $this->context->setConfig($cfg);
        $this->processCookie();
        $this->processRequest();

        // start controller and render
        $ctrl = $this->context->getController();
        $ctrl->render();
    }

    private function processCookie() {
        if(isset($_COOKIE['thegrid_userId']) && isset($_COOKIE['thegrid_token'])) {
            $this->context->loadModels(array('user'));
            $this->context->loadHelpers(array('response'));
            $this->context->user = $this->context->models['user']->getSingle(array(
                            'id'=>$_COOKIE['thegrid_userId'],
                            'token'=>$_COOKIE['thegrid_token']), false);
            if(!isset($this->context->user['id'])) {
                setcookie("thegrid_userId", "", time() - 3600);
                setcookie("thegrid_token", "", time() - 3600);
            }
            $this->context->user = $this->context->user;
        }
    }

    private function processRequest() {
        $requestParts = explode('?', $_SERVER['REQUEST_URI']);
        $config = $this->context->getConfig();
        $url = substr($requestParts[0], strlen($config['appPath']));
        $path = explode('/', $url);

        $actionIndex = $this->processController($path);

        if ($actionIndex < 0)
            return;

        $actionResult = $this->processAction($path, $actionIndex);

        if(!$actionResult)
            return;

        $varIndex = $actionIndex + 1;
        $this->processRequestVars($path, $varIndex);
    }

    private function processController($path) {
        $config = $this->context->getConfig();
        $actionIndex = 1;

        if(empty($path[0]) || $path[0] == 'index.php') {
            $this->context->setController('Index');
        }
        else if(file_exists(getcwd()."\\c\\{$path[0]}.php")) {
            $this->context->setController($path[0]);
        }
        else {
            $this->context->setMessage('debug', count($path));
            $this->context->setMessage('debug', getcwd()."\\c\\{$path[0]}\\{$path[1]}.php");
            $this->context->setMessage('debug', file_exists(getcwd()."\\c\\{$path[0]}\\{$path[1]}.php"));
            header("HTTP/1.0 404 Not Found");
            require_once '/c/error.php';
            $this->context->setController('Error');
            $this->context->setAction('http404');
            $this->context->setParam('message', 'Requested page '.$config['appPath'].'<b>'.$path[0].'</b> not found.');
            return -1;
        }

        return $actionIndex;
    }

    private function processAction($path, $actionIndex) {
        $config = $this->context->getConfig();
        $actionName = 'index';
        if(count($path) > $actionIndex && !empty($path[$actionIndex])) {
            $actionName = $path[$actionIndex];
        }

        if(method_exists($this->context->getControllerClass(), $actionName)) {
            $this->context->setAction($actionName);
        }
        else {
            header("HTTP/1.0 404 Not Found");
            require_once '/c/error.php';
            $this->context->setController('Error');
            $this->context->setAction('http404');
            $this->context->setParam('message', 'Requested action <b>'.$actionName.'</b> not found in page '.$config['appPath'].$this->context->controllerName.'.');
            return false;
        }

        return true;
    }

    private function processRequestVars($path, $varIndex) {
        for ($i=$varIndex; $i<count($path); $i++) {
            $this->context->setParam('var'.($i-$varIndex), $path[$i]);
        }

        foreach ($_REQUEST as $key=>$val) {
            $this->context->setParam($key, $val);
        }
    }
}
