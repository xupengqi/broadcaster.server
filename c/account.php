<?php
class AccountController extends RESTController {

    public function login($params) {
        $params = $this->checkParameters($params, array('username'), array('password'=>''));
        $this->context->loadModels(array('user'));
        $this->context->loadHelpers(array('session', 'response'));

        $user = $this->context->models['user']->getSingle(array('username'=>$params['username']), false);
        $this->context->helpers['session']->authenticate($user, $params['password']);
        if(!$this->context->helpers['response']->hasError()) {
            $user['token'] = $this->context->helpers['session']->start($user['username']);
            $this->context->helpers['response']->setData('user', $this->getUserResult($user));
        }

        $this->context->helpers['response']->flush();
    }

    public function loginfb($params) {
        $params = $this->checkParameters($params, array('data'=>array('username', 'fbId', 'token')), array('email'=>''));
        $this->context->loadModels(array('user'));
        $this->context->loadHelpers(array('session', 'response', 'security'));

        $user = $this->context->models['user']->getSingle(array('fbId'=>$params['data']['fbId']), false);
        if (empty($user)) {
            $params['data']['salt'] = $this->context->helpers['security']->generateSalt();
            $params['data']['pass'] = $this->context->helpers['security']->crypt($this->context->helpers['security']->generateSalt(), $params['data']['salt']);
            $params['data']['lastLogin'] = $this->DB_KEY_NOW;
            $user = $this->context->models['user']->createPossibleDupeUser($params['data']);
        }
        else {
            $this->context->models['user']->update(array('fbId'=>$params['data']['fbId']), array('token'=>$params['data']['token']));
            $user['token'] = $params['data']['token'];
        }
        $this->context->helpers['response']->setData('user', $this->getUserResult($user));
        $this->context->helpers['response']->flush();
    }

    public function loginGPlus($params) {
        $params = $this->checkParameters($params, array('data'=>array('username', 'gPlusId', 'token')), array('email'=>''));
        $this->context->loadModels(array('user'));
        $this->context->loadHelpers(array('session', 'response', 'security'));

        $user = $this->context->models['user']->getSingle(array('gPlusId'=>$params['data']['gPlusId']), false);
        if (empty($user)) {
            $params['data']['salt'] = $this->context->helpers['security']->generateSalt();
            $params['data']['pass'] = $this->context->helpers['security']->crypt($this->context->helpers['security']->generateSalt(), $params['data']['salt']);
            $params['data']['lastLogin'] = $this->DB_KEY_NOW;
            $user = $this->context->models['user']->createPossibleDupeUser($params['data']);
        }
        else {
            $this->context->models['user']->update(array('gPlusId'=>$params['data']['gPlusId']), array('token'=>$params['data']['token']));
            $user['token'] = $params['data']['token'];
        }
        $this->context->helpers['response']->setData('user', $this->getUserResult($user));
        $this->context->helpers['response']->flush();
    }
    
    public function removeGPlus($params) {
        $this->context->loadModels(array('user'));
        $this->context->loadHelpers(array('response', 'session'));
        $user = $this->context->helpers['session']->authenticateWithParams($params);
        $this->context->models['user']->update(array('gPlusId'=>$user['gPlusId']), array('gPlusId'=>$this->DB_KEY_NULL));
        $user['gPlusId'] = '';
        $user['token'] = $this->context->helpers['session']->start($user['username']);
        $this->context->helpers['response']->setData('user', $this->getUserResult($user));
        $this->context->helpers['response']->flush();
    }
    
    public function removeFB($params) {
        $this->context->loadModels(array('user'));
        $this->context->loadHelpers(array('response', 'session'));
        $user = $this->context->helpers['session']->authenticateWithParams($params);
        $this->context->models['user']->update(array('fbId'=>$user['fbId']), array('fbId'=>$this->DB_KEY_NULL));
        $user['fbId'] = '';
        $user['token'] = $this->context->helpers['session']->start($user['username']);
        $this->context->helpers['response']->setData('user', $this->getUserResult($user));
        $this->context->helpers['response']->flush();
    }

    public function logout($params) {
        $this->context->loadHelpers(array('session'));
        $this->context->helpers['session']->stop();
    }

    public function register($params) {
        $params = $this->checkParameters($params, array('data'=>array('username', 'pass')), array('data'=>array('email'=>'')));
        $this->context->loadHelpers(array('security', 'session', 'response'));
        $this->context->loadModels(array('user'));

        $user = $this->context->models['user']->getMulti(array('username'=>$params['data']['username']));
        if (count($user) > 0) {
            $this->context->helpers['response']->setError('USERNAME_EXISTS');
            $this->context->helpers['response']->flush();
            exit;
        }

        $params['data']['salt'] = $this->context->helpers['security']->generateSalt();
        $params['data']['pass'] = $this->context->helpers['security']->crypt($params['data']['pass'], $params['data']['salt']);
        $params['data']['lastLogin'] = $this->DB_KEY_NOW;
        $user = array();
        $user['id'] = $this->context->models['user']->create($params['data']);
        if($user['id'] > 0) {
            $user['username'] = $params['data']['username'];
            $user['email'] = $params['data']['email'];
            $user['token'] = $this->context->helpers['session']->start($params['data']['username']);
            $this->context->helpers['response']->setData('user', $user);
        }

        $this->context->helpers['response']->flush();
    }

    public function feedback($params) {
        $params = $this->checkParameters($params, array('data'=>array('text')));
        $this->context->loadHelpers(array('response'));
        $this->context->loadModels(array('feedback'));
        $this->context->models['feedback']->create($params['data']);
        $this->context->helpers['response']->flush();
    }

    public function updateUsername($params) {
        $params = $this->checkParameters($params, array());
        $this->context->loadModels(array('user'));
        $this->context->loadHelpers(array('response', 'session'));
        $this->context->helpers['session']->authenticateWithParams($params);
        $this->context->models['user']->updateUsername($params['userId'], $params['data']);
        $this->context->helpers['response']->flush();
    }
    
    public function updateEmail($params) {
        $params = $this->checkParameters($params, array(), array('data'=>array('email'=>'')));
        $this->context->loadModels(array('user'));
        $this->context->loadHelpers(array('response', 'session'));
        $this->context->helpers['session']->authenticateWithParams($params);
        $this->context->models['user']->update($params['userId'], $params['data']);
        $this->context->helpers['response']->flush();
    }

    public function updatePassword($params) {
        $params = $this->checkParameters($params, array(), array('data'=>array('pass'=>'')));
        $this->context->loadModels(array('user'));
        $this->context->loadHelpers(array('response', 'session', 'security'));
        $this->context->helpers['session']->authenticateWithParams($params);
        $params['data']['salt'] = $this->context->helpers['security']->generateSalt();
        $params['data']['pass'] = $this->context->helpers['security']->crypt($params['data']['pass'], $params['data']['salt']);
        $this->context->models['user']->update($params['userId'], $params['data']);
        $this->context->helpers['response']->flush();
    }

    private function getUserResult($user) {
        $user = $this->checkParameters($user, array(), array('fbId'=>'', 'gPlusId'=>'', 'usernameChange'=>'0'));
        return array(
                        'id'=>$user['id'],
                        'fbId'=>$user['fbId'],
                        'gPlusId'=>$user['gPlusId'],
                        'username'=>$user['username'],
                        'usernameChange'=>$user['usernameChange'],
                        'email'=>$user['email'],
                        'token'=>$user['token']);
    }
}