<?php
class TagsController extends RESTController {
    protected $layout = '';

    public function index($params) {
        $this->context->loadModels(array('post','vote'));
        $this->context->loadHelpers(array('session'));
        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $this->all($params);
                    break;
            }
        }
        catch (Exception $ex) {
            echo 'Caught exception: ',  $ex->getMessage(), "\n";
        }
    }

    public function all($params) {
        $this->context->loadModels(array('tag'));
        $this->context->loadHelpers(array('session', 'response'));
    
        $tags = $this->context->models['tag']->getMulti(array(), false, "ORDER BY count DESC LIMIT 20");
        $tagsResult = array();
        foreach($tags as $key=>$value) {
            $tagsResult[] = $tags[$key]['name'];
        }

        $this->context->helpers['response']->setData('tags', $tagsResult);
        $this->context->helpers['response']->flush();
    }
}
