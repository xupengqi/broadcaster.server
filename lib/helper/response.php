<?php
class ResponseHelper extends Helper {
    PRIVATE $errors;
    private $data;
    
    public function __construct() {
        $this->errors = array();
    }
    
    public function setError($error_id, $custom_msg = '', $sql_error_id = 0) {
        $this->error_id = $error_id;
        
        switch($error_id) {
            case $this->ERR_ID_WRONG_PASS:
                $this->setErrorMessage($error_id, '', 'Wrong password.', $custom_msg);
                break;
            case $this->ERR_ID_USER_NOT_FOUND:
                $this->setErrorMessage($error_id, '', 'User not found.', $custom_msg);
                break;
            case $this->ERR_ID_SQL_ERROR:
                $this->setErrorMessage($error_id, $sql_error_id, 'Database error.', $custom_msg);
                break;
            case $this->ERR_ID_REQUIRE_LOGIN:
                $this->setErrorMessage($error_id, 'REQUIRE_LOGIN', 'Please login.', $custom_msg);
                break;
            case $this->ERR_ID_MISSING_PARAMETER:
                $this->setErrorMessage($error_id, 'MISSING_PARAMETER', 'Missing parameter.', $custom_msg);
                break;
            case $this->ERR_ID_RESOURCE_NOT_FOUND:
                $this->setErrorMessage($error_id, 'RESOURCE_NOT_FOUND', 'Can\'t find what you looking for!', $custom_msg);
                break;
            case $this->ERR_ID_USER_ALREADY_EXIST:
                $this->setErrorMessage($error_id, '', 'User already exist.', $custom_msg);
                break;
        }
    }
    
    private function setErrorMessage($error_id, $error_code, $error_msg, $error_custom_msg) {
        $this->errors[] = array('id'=>$error_id, 'code'=>$error_code, 'msg'=>$error_msg, 'custom_msg'=>$error_custom_msg);
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