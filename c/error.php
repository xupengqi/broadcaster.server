<?php
class ErrorController extends RESTController {
    public function http404($params) {
        $this->context->setMessage('Error', $params['message']);
    }
    
    public function report($params) {
        $params = $this->checkParameters($params, array('data'=>array('model', 'manufacture', 'product', 'stacktrace')));
        $this->context->loadHelpers(array('response'));
        $this->context->loadModels(array('error'));
        $this->context->models['error']->create($params['data']);
        $this->context->helpers['response']->flush();
    }
}
