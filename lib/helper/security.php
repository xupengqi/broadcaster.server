<?php
class SecurityHelper extends Helper {
	public function crypt($str, $salt) {
		return crypt($str, $salt);
	}
	
	public function generateSalt($len = 32) {
		 return substr(hash('sha512',uniqid(rand(), true).microtime()), 0, $len);
	}
}