<?php
class ResponseHelper extends Helper {
    PRIVATE $errors;
    private $data;
    
    public function __construct() {
        $this->errors = array();
    }
    
    public function setError($error_code) {
        $this->errors[] = array('code'=>$error_code);
    }
    
    public function clearLastError() {
        array_pop($this->errors);
    }
    
    public function setData($key, $val) {
        $this->data[$key] = $val;
    }
    
    public function flush() {
        ob_end_clean();
        echo json_encode(array(
            'errors' => $this->errors,
            'data' => ($this->data == null)?(new stdClass):$this->data
        ));
    }
    
    public function hasError() {
        return (count($this->errors) > 0);
    }
}