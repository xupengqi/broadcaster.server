<?php
class SessionHelper extends Helper {

    public function authenticate($user, $password) {
        $this->context->loadHelpers(array('security', 'response'));

        if(count($user) > 0) {
            $pass = $this->context->helpers['security']->crypt($password, $user['salt']);
            if($user['pass'] != $pass) {
                $this->context->helpers['response']->setError($this->ERR_ID_WRONG_PASS);
            }
        }
        else {
            $this->context->helpers['response']->setError($this->ERR_ID_USER_NOT_FOUND);
        }
    }

    public function stop() {
        $this->context->loadModels(array('user'));
        $this->context->models['user']->update($this->context->user['id'], array('token'=>$this->DB_KEY_NULL));
        //session_destroy();
    }

    public function start($username) {
        $this->context->loadModels(array('user'));
        $this->context->loadHelpers(array('security'));

        $token = $this->context->helpers['security']->crypt(
                        $this->context->helpers['security']->generateSalt(),
                        $this->context->helpers['security']->generateSalt());
        $this->context->models['user']->update(array('username'=>$username), array('token'=>$token));

        return $token;
    }

    public function isLoggedIn() {
        return $this->context->user;
    }

    public function getUsername() {
        return $this->context->user['username'];
    }

    public function authenticateWithParams($params) {
        $this->context->loadModels(array('user'));
        $user = $this->context->models['user']->getSingle(array('id'=>$params['userId'], 'token'=>$params['token']));
        if (empty($user)) {
            $this->context->loadHelpers(array('response'));
            $this->context->helpers['response']->setError($this->ERR_ID_REQUIRE_LOGIN);
            $this->context->helpers['response']->flush();
            exit;
        }
        return $user;
    }
}