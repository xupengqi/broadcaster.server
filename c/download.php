<?php
class DownloadController extends RESTController {
    protected $layout = '';
    
    public function index($params) {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getData($params);
                break;
        }
    }
    
    private function getData($params) {
        $this->context->loadHelpers(array('session', 'response'));
        
        $dir = $params['data']['dir'];
        $id = $params['data']['id'];
        $type = $params['data']['type'];
        $ext = 'dat';
        switch($type) {
            case 'IMAGE':
                $ext = 'jpg';
                break;
            case 'AUDIO':
                $ext = '3gp';
                break;
            case 'VIDEO':
                $ext = 'mp4';
                break;
        }
        $data = file_get_contents("data\\$dir\\$id.$ext");
        error_log("download {$data} size=".strlen($data));
        
        $this->context->helpers['response']->setData($id,base64_encode($data));
        $this->context->helpers['response']->flush();
    }
}
    