<?php
class PostsController extends RESTController {
    protected $layout = '';

    public function index($params) {
        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    break;
                case 'PUT':
                case 'POST':
                    $this->indexPost($params);
                    break;
                case 'DELETE':
                    $this->indexDelete($params);
                    break;
            }
        }
        catch (Exception $ex) {
            echo 'Caught exception: ',  $ex->getMessage(), "\n";
        }
    }

    private function indexPost($params) {
        $params = $this->checkParameters($params,
                        array('token', 'tags', 'data'=>array('userId', 'title', 'latitude', 'longitude', 'location')),
                        array('userId'=>$params['data']['userId'], 'data'=>array('content'=>array('text'=>''))));
        $this->context->loadModels(array('post','vote', 'post_tag'));
        $this->context->loadHelpers(array('session', 'response'));
        $this->context->helpers['session']->authenticateWithParams($params);
         
        $params['data']['content'] = $this->processPostContent($params['data']['content']);

        $postId = $this->context->models['post']->create($params['data']);
        $voteId = $this->context->models['vote']->create(array('userId'=>$params['data']['userId'],'postId'=>$postId,'voteDir'=>1));

        $tags = $this->processTags($params['tags']);
        foreach($tags as $tag) {
            $voteId = $this->context->models['post_tag']->create(array('postId'=>$postId,'tagId'=>$tag['id']));
        }

        if($postId > 0 && $voteId > 0) {
            $this->context->helpers['response']->setData('postId', $postId);
        }

        $this->context->helpers['response']->flush();
    }

    private function indexDelete($params) {
        $params = $this->checkParameters($params, array('userId', 'token', 'id'), array('data'=>array('visibility'=>-1)));
        $this->context->loadModels(array('post'));
        $this->context->loadHelpers(array('session', 'response'));
        $authenticated = $this->context->helpers['session']->authenticateWithParams($params);

        $this->context->models['post']->update($params['id'], $params['data']);
        if (isset($params['parentId'])) {
            $this->context->models['post']->incComment($params['parentId'], '-');
        }
        $this->context->helpers['response']->flush();
    }

    public function unDelete($params) {
        $params = $this->checkParameters($params, array('userId', 'token', 'id'), array('data'=>array('visibility'=>0)));
        $this->context->loadModels(array('post'));
        $this->context->loadHelpers(array('session', 'response'));
        $authenticated = $this->context->helpers['session']->authenticateWithParams($params);

        $this->context->models['post']->update($params['id'], $params['data']);
        if (isset($params['parentId'])) {
            $this->context->models['post']->incComment($params['parentId'], '+');
        }
        $this->context->helpers['response']->flush();
    }

    public function byLocation($params) {
        $params = $this->checkParameters($params, array('lat', 'lng', 'radius', 'tag'), array('after'=>0));
        $this->context->loadModels(array('post_active', 'post_tag'));
        $this->context->loadHelpers(array('response'));

        $radius = $this->getRadius($params['lat'], $params['lng'], $params['radius']); //TODO : SET MAX RADIUS
        $params['swlat'] = $params['lat'] - $radius['lat'];
        $params['nelat'] = $params['lat'] + $radius['lat'];
        $params['swlng'] = $params['lng'] - $radius['lng'];
        $params['nelng'] = $params['lng'] + $radius['lng'];
        $posts = $this->getPostsInBoundRoutine($params);

        $this->context->helpers['response']->setData('posts', $posts);
        $this->context->helpers['response']->flush();
    }

    public function byBounds($params) {
        $params = $this->checkParameters(
                        $params,
                        array('nelat', 'nelng', 'swlat', 'swlng'),
                        array('after'=>0, 'filter'=>false, 'tag'=>'[[RESERVED_TAG_EVERYTHING]]'));
        $this->context->loadModels(array('post_active', 'post_tag'));
        $this->context->loadHelpers(array('response'));

        $posts = $this->getPostsInBoundRoutine($params);
        if ($params['filter']) {
            $filteredPosts = array();
            if($params['swlat'] > $params['nelat']) {
                $filteredPosts = $this->filterPosts($params['swlat'], $params['swlng'], $params['nelat'], 180, $posts);
                $filteredPosts = $filteredPosts + $this->filterPosts($params['swlat'], -180, $params['nelat'], $params['nelng'], $posts);
            }
            else {
                $filteredPosts = $this->filterPosts($params['swlat'], $params['swlng'], $params['nelat'], $params['nelng'], $posts);
            }
            $posts = $filteredPosts;
        }

        $this->context->helpers['response']->setData('posts', $posts);
        $this->context->helpers['response']->flush();
    }

    public function byParent($params) {
        $params = $this->checkParameters($params, array('parentId'), array('includeParent'=>true));
        $this->context->loadModels(array('post_active', 'post_tag'));
        $this->context->loadHelpers(array('response'));

        $cond = array('parentId'=>$params['parentId']);
        $cond = $this->processAfter($params, $cond);
        $comments = $this->context->models['post_active']->getMulti($cond, false, 'ORDER BY id ASC LIMIT 10');

        if ($params['includeParent']) {
            $post = $this->context->models['post_active']->getSingle(array('id'=>$params['parentId']));
            $this->context->helpers['response']->setData('post', $post);
        }

        $this->context->helpers['response']->setData('posts', $comments);
        $this->context->helpers['response']->flush();
    }

    public function byId($params) {
        $params = $this->checkParameters($params);
        $this->context->loadModels(array('post_active', 'post_tag'));
        $this->context->loadHelpers(array('response'));

        if (!empty($params['ids'])) {
            $cond = array('id'=>"({$params['ids']})", 'operators'=>array('id'=>'IN'));
            $posts = $this->context->models['post_active']->getMulti($cond, false);
            $this->context->helpers['response']->setData('posts', $posts);
        }

        $this->context->helpers['response']->flush();
    }

    public function byUser($params) {
        $params = $this->checkParameters($params, array('userId'));
        $this->context->loadModels(array('post_active', 'post_tag', 'user'));
        $this->context->loadHelpers(array('response'));

        $cond = array('userId'=>$params['userId']);
        $cond = $this->processBefore($params, $cond);
        $posts = $this->context->models['post_active']->getMulti($cond, false, 'LIMIT 10');

        $this->context->helpers['response']->setData('posts', $posts);
        $this->context->helpers['response']->flush();
    }

    public function update($params) {
        $params = $this->checkParameters($params, array('userId', 'token', 'id'), array('data'=>array('content'=>array('text'=>''))));
        $this->context->loadModels(array('post', 'vote', 'post_tag'));
        $this->context->loadHelpers(array('session', 'response'));
        $this->context->helpers['session']->authenticateWithParams($params);

        $currentPost = $this->context->models['post']->getSingle(array('id'=>$params['id']));
        $params['data']['content'] = $this->processPostContent($params['data']['content'], (array)$currentPost['content']);
        $this->context->models['post']->update($params['id'], $params['data']);

        $this->context->models['post_tag']->delete(array('postId'=>$params['id']));
        $tags = $this->processTags($params['tags']);
        foreach($tags as $tag) {
            $voteId = $this->context->models['post_tag']->create(array('postId'=>$params['id'],'tagId'=>$tag['id']));
        }

        $this->context->helpers['response']->setData('postId', $params['id']);
        $this->context->helpers['response']->flush();
    }

    public function attach($params) {
        $params = $this->checkParameters($params, array('userId', 'token'), array('data'=>array('content'=>array('text'=>''))));
        $this->context->loadModels(array('post'));
        $this->context->loadHelpers(array('session', 'response'));
        $this->context->helpers['session']->authenticateWithParams($params);
        //error_log(print_r($params,true));

        $post = $this->context->models['post']->getSingle(array('id'=>$params['data']['postId']));
        $id = uniqid();
        $content = $this->addAttachmentToContent($id, $params['data']['content']['attachments'][0]['type'], (array)$post['content']);
        $this->context->models['post']->update($post['id'], array('content'=>$content));
        $this->context->helpers['response']->setData('attachId', $id);
        $this->context->helpers['response']->flush();
    }

    public function thumb($params) {
        $params = $this->checkParameters($params, array('userId', 'token'), array('data'=>array('content'=>array('text'=>''))));
        $this->context->loadModels(array('post'));
        $this->context->loadHelpers(array('session', 'response'));
        $this->context->helpers['session']->authenticateWithParams($params);
        $post = $this->context->models['post']->getSingle(array('id'=>$params['postId']));
        $content = (array)$post['content'];
        foreach($content['attachments'] as $attachment) {
            $attachment = (array)$attachment;
            if ($params['attachId'] == $attachment['id']) {
                $filePath = "data\\{$attachment['dir']}\\t\\{$attachment['id']}.jpg";
                $this->saveFromPutStream($filePath);
                break;
            }
        }
        $this->context->helpers['response']->flush();
    }

    public function deleteAttach($params) {
        $params = $this->checkParameters($params, array('userId', 'token'), array('data'=>array('content'=>array('text'=>''))));
        $this->context->loadModels(array('post'));
        $this->context->loadHelpers(array('session', 'response'));
        $this->context->helpers['session']->authenticateWithParams($params);

        $post = $this->context->models['post']->getSingle(array('id'=>$params['data']['postId']));
        $content = $this->deleteAttachmentFromContent($params['data']['content']['attachments'][0]['id'], (array)$post['content']);
        $this->context->models['post']->update($post['id'], array('content'=>$content));
        $this->context->helpers['response']->flush();
    }

    public function reply($params) {
        $params = $this->checkParameters($params,
                        array('token', 'data'=>array('userId', 'parentId', 'title')),
                        array('userId'=>$params['data']['userId']));
        $this->context->loadModels(array('post'));
        $this->context->loadHelpers(array('session', 'response'));
        $this->context->helpers['session']->authenticateWithParams($params);

        $params['data']['content'] = $this->processPostContent($params['data']['content']);
        $postId = $this->context->models['post']->create($params['data']);
        $this->context->models['post']->incComment($params['data']['parentId']);
        if($postId > 0) {
            $this->context->helpers['response']->setData('postId', $postId);
        }
        $this->context->helpers['response']->flush();
    }

    private function getRadius($lat1, $lon1, $d) {
        $north = $this->getNewCoord($lat1, $lon1, $d, 0);
        $east = $this->getNewCoord($lat1, $lon1, $d, 90);
        if ($east['lng'] < $lon1) {
            $east['lng'] += 360;
        }
        return array('lat'=>$north['lat']-$lat1, 'lng'=>$east['lng']-$lon1);
    }

    private function getNewCoord($lat1, $lon1, $d, $brng) {
        $R = 6371;
        $brng = deg2rad($brng);
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = asin( sin($lat1)*cos($d/$R) + cos($lat1)*sin($d/$R)*cos($brng) );
        $lon2 = $lon1 + atan2(sin($brng)*sin($d/$R)*cos($lat1), cos($d/$R)-sin($lat1)*sin($lat2));
        //error_log("lat1:$lat1, lon1:$lon1, d:$d, brng:$brng ----> lat2:$lat2, lon2:$lon2");
        return array('lat'=>rad2deg($lat2), 'lng'=>rad2deg($lon2));
    }

    private function filterPosts($swlat, $swlng, $nelat, $nelng, $posts) {
        $steps = array('latstep'=>($nelat-$swlat)/8, 'lngstep'=>($nelng-$swlng)/8);
        //error_log('steps:'.print_r($steps,true));
        $grid = array();
        foreach($posts as $post) {
            $gridLatNum = ceil($post['latitude'] / $steps['latstep']);
            $gridLngNum = ceil($post['longitude'] / $steps['lngstep']);
            if($gridLatNum * $steps['latstep'] == $post['latitude'])
                $gridLatNum++;
            if($gridLngNum * $steps['lngstep'] == $post['longitude'])
                $gridLngNum++;
            if(!isset($grid[$gridLatNum.$gridLngNum]) || $grid[$gridLatNum.$gridLngNum]['id'] < $post['id'])
                $grid[$gridLatNum.$gridLngNum] = $post;
        }

        $result = array();
        foreach($grid as $post) {
            $result[] = $post;
        }
        return $result;
    }

    private function processBefore($params, $cond) {
        if (isset($params['after'])) {
            $cond['id'] = $params['after'];
            $cond['operators'] = array('id'=>'<');
        }
        return $cond;
    }

    private function processAfter($params, $cond) {
        if (isset($params['after'])) {
            $cond['id'] = $params['after'];
            $cond['operators'] = array('id'=>'>');
        }
        return $cond;
    }
    
    private function processPostContent($params, $baseContent = array()) {
        $content = $baseContent;

        $content['text'] = $params['text'];

        return json_encode($content);
    }

    private function getPostsInBoundRoutine($params) {
        $this->context->loadModels(array('post'));

        $defaultNumResults = 10;
        return $this->context->models['post']->routine('getPostsInBound', array(
                        $params['swlat'],
                        $params['swlng'],
                        $params['nelat'],
                        $params['nelng'],
                        $defaultNumResults,
                        $params['after'],
                        $params['tag'] //TODO: CHANGE TO TAGS
        ));
    }

    private function processTags($tagsStr) {
        $this->context->loadModels(array('tag'));
        $this->context->loadHelpers(array('response'));

        $tags = explode(',', $tagsStr);
        foreach($tags as $i=>$tag) {
            $tags[$i] = $this->context->models['tag']->real_escape_string(strtolower($tag));
        }
        $tagsImploded = implode("','",$tags);
        $foundTags = $this->context->models['tag']->getMulti(array('name'=>"('$tagsImploded')",'operators'=>array('name'=>'IN')));

        $foundTagNames = array();
        foreach($foundTags as $tag) {
            $foundTagNames[] = $tag['name'];
        }
         
        foreach($tags as $tag) {
            if(!empty($tag) && !in_array($tag, $foundTagNames)) {
                $this->context->models['tag']->create(array('name'=>$tag));
            }
        }

        $foundTags = $this->context->models['tag']->getMulti(array('name'=>"('$tagsImploded')",'operators'=>array('name'=>'IN')));
        return $foundTags;
    }

    private function addAttachmentToContent($id, $type, $content) {
        if (!isset($content['attachments'])) {
            $content['attachments'] = array();
        }

        $dir = date('Y.m.d');
        if(!is_dir("data")) {
            mkdir("data");
        }
        if(!is_dir("data\\$dir")) {
            mkdir("data\\$dir");
        }
        if(!is_dir("data\\$dir\\t")) {
            mkdir("data\\$dir\\t");
        }

        $attachment = array();
        $attachment['type'] = $type;
        $attachment['dir'] = $dir;
        $attachment['id'] = $id;
        $content['attachments'][] = $attachment;

        switch($attachment['type']) {
            case 'IMAGE':
                $filePath = "data\\$dir\\{$attachment['id']}.jpg";
                $this->saveFromPutStream($filePath);
                //$thumbPath = "data\\$dir\\t\\{$attachment['id']}.jpg";
                //$this->createThumbnail($filePath, $thumbPath, 300);
                break;
            case 'VIDEO':
                $ext = 'mp4';
                $filePath = "data\\$dir\\{$attachment['id']}.$ext";
                $this->saveFromPutStream($filePath);
                // TODO: CREATE THUMB HERE
                //$thumbPath = "data\\$dir\\t\\{$attachment['id']}.jpg";
                //$file = fopen($thumbPath, 'wb');
                //fwrite($file, base64_decode($attachment['content_thumb']));
                //fclose($file);
                break;
            case 'AUDIO':
                $ext = '3gp';
                $filePath = "data\\$dir\\{$attachment['id']}.$ext";
                $this->saveFromPutStream($filePath);
                break;
        }

        return json_encode($content);
    }

    private function deleteAttachmentFromContent($aId, $content) {
        foreach($content['attachments'] as $i=>$attachment) {
            $attachment = (array)$attachment;
            if ($aId == $attachment['id']) {
                /*switch($attachment['type']) {
                    case 'IMAGE':
                        $filePath = "data\\{$attachment['dir']}\\{$attachment['id']}.jpg";
                        unlink($filePath);
                        //$thumbPath = "data\\{$attachment['dir']}\\t\\{$attachment['id']}.jpg";
                        //unlink($thumbPath);
                        break;
                    case 'VIDEO':
                        $ext = 'mp4';
                        $filePath = "data\\{$attachment['dir']}\\{$attachment['id']}.$ext";
                        $thumbPath = "data\\{$attachment['dir']}\\t\\{$attachment['id']}.jpg";
                        unlink($filePath);
                        unlink($thumbPath);
                        break;
                    case 'AUDIO':
                        $ext = '3gp';
                        $filePath = "data\\{$attachment['dir']}\\{$attachment['id']}.$ext";
                        unlink($filePath);
                        break;
                }*/
                array_splice($content['attachments'], $i, 1);
                break;
            }
        }
        return json_encode($content);
    }

    private function saveFromPutStream($filePath) {
        $putdata = fopen("php://input", "r");
        $file = fopen($filePath, 'wb');
        while ($data = fread($putdata, 1024)) {
            fwrite($file, $data);
        }
        fclose($file);
        fclose($putdata);
    }

    private function createThumbnail($src, $dest, $desired_height) {
        /* read the source image */
        $source_image = imagecreatefromjpeg($src);
        $width = imagesx($source_image);
        $height = imagesy($source_image);
         
        /* find the "desired height" of this thumbnail, relative to the desired width  */
        $desired_width = floor($width * ($desired_height / $height));
         
        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
         
        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
         
        /* create the physical thumbnail image to its destination */
        imagejpeg($virtual_image, $dest);
    }
}
