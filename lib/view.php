<?php
class View extends MVC {

	public function render($data) {
		if(!empty($this->context->layout))
			require_once '/v/layout/'.$this->context->layout.'.php';
	}
	
	public function layoutView($viewLabel, $data = false) {
		$config = $this->context->getConfig();
		if(!isset($this->context->views[$viewLabel])) {
			$this->context->setMessage('Error', "View <b>$viewLabel</b> is not defined.");
			if($config['debug']) {
				$this->context->setMessage('Debug', "Views: ".print_r($this->env['views'],true));
			}
			return;
		}
		$this->renderView($this->context->views[$viewLabel], $data);
	}
	
	public function renderView($viewId, $data = array(), $return = false) {
		$config = $this->context->getConfig();
		$viewName = $viewId.'View';
		$viewFile = '/v/'.$viewId.'.php';
		if (!file_exists($_SERVER['DOCUMENT_ROOT'].$config['appPath'].$viewFile)) {
			$this->context->setMessage('Error', "View <b>{$viewId}</b> is not found.");
			if($config['debug']) {
				$this->context->setMessage('Debug', "View file: ".$viewFile);
			}
		}
		else {
			if($return) {
				ob_start();
				require $viewFile;
				return ob_get_clean();
			}
			else {
				require $viewFile;
			}
		}
	}
	
	protected function renderMessages() {
		$messages = $this->context->getMessages();
		foreach ($messages as $type => $typeMessages) {
			foreach ($typeMessages as $message) {
				echo "<p>{$type}: {$message}</p>";
			}
		}
	}
	
	private function js($file, $external = false) {
		$config = $this->context->getConfig();
		if($external) {
			return '<script src="'.$file.'" type="text/javascript"></script>';
		}
		else {
			return '<script src="'.$config['appPath'].'js/'.$file.'.js" type="text/javascript"></script>';
		}
	}
	
	private function css($file, $external = false) {
		$config = $this->context->getConfig();
		if($external) {
			return '<link href="'.$file.'" type="text/css" rel="stylesheet">';
		}
		else {
			return '<link href="'.$config['appPath'].'css/'.$file.'.css"  type="text/css" rel="stylesheet">';
		}
	}
}
