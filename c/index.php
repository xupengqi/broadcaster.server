<?php
class IndexController extends Controller {

    public function index($var) {
        $data = array();
        if(isset($var['lat']) && isset($var['lng'])) {
            $data['lat'] = $var['lat'];
            $data['lng'] = $var['lng'];
        }

        return $data;
    }
}