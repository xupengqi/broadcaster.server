<?php

class RESTController extends Controller {
    protected $layout = '';

    protected function checkParameters($params, $expected = array(), $default = array()) {
        foreach ($expected as $key=>$param) {
            if (is_array($param)) {
                if (!isset($params[$key])) {
                    $this->parameterError($key);
                }
                else {
                    $this->checkParameters($params[$key], $param);
                }
            }
            else if (!isset($params[$param]) || empty($params[$param])) {
                $this->parameterError($param);
            }
        }
        return $this->setDefault($params, $default);
    }

    protected function setDefault($params, $default) {
        foreach ($default as $key=>$val) {
            if (!isset($params[$key])) {
                $params[$key] = $val;
            }
            else if (is_array($val)) {
                $params[$key] = $this->setDefault($params[$key], $default[$key]);
            }
        }
        return $params;
    }

    protected function parameterError($param) {
        $this->context->loadHelpers(array('response'));
        $this->context->helpers['response']->setError('INTERNAL_ERROR');
        $this->context->helpers['response']->flush();
        exit;
    }
}