<?php
require_once '/c/posts.php';

class TestController extends PostsController {

    public function index() {
        $this->gen();
    }

    public function gen() {
        $view = new View($this->context);
        $view->renderView('testgen', array());
    }

    public function find() {
        $view = new View($this->context);
        $view->renderView('testfind', array());
    }

    public function post($params) {
        $this->context->loadModels(array('post','vote', 'post_tag', 'user'));
        $this->context->loadHelpers(array('session', 'response'));

        $user = $this->context->models['user']->getRandomUser();
        $params['data']['userId'] = $user['id'];
        $params['data']['latitude'] = number_format((float)$params['data']['latitude'], $params['data']['privacy']);
        $params['data']['longitude'] = number_format((float)$params['data']['longitude'], $params['data']['privacy']);
        $tags = 'default';
        unset($params['data']['privacy']);
        unset($params['data']['tags']);
        
        $params = $this->processNewPostContent($params);

        $postId = $this->context->models['post']->create($params['data']);
        $voteId = $this->context->models['vote']->create(array('userId'=>$params['data']['userId'],'postId'=>$postId,'voteDir'=>1));
        
        //$this->context->helpers['response']->setError($this->ERR_ID_MISSING_PARAMETER, print_r($tags,true));
        $tags = $this->processTags($tags);
        foreach($tags as $tag) {
            $voteId = $this->context->models['post_tag']->create(array('postId'=>$postId,'tagId'=>$tag['id']));
        }
        
        if($postId > 0 && $voteId > 0) {
            $this->context->helpers['response']->setData('postId', $postId);
        }
        
        $this->context->helpers['response']->flush();
    }

    public function reply() {
        $this->context->loadModels(array('post', 'user'));
        $user = $this->context->models['user']->getRandomUser();
        $post = $this->context->models['post']->getRandomParentPost();
        $_REQUEST['data']['userId'] = $user['id'];
        $_REQUEST['data']['parentId'] = $post['id'];
        $this->context->models['post']->create($_REQUEST['data']);
    }
}