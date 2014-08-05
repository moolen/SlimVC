<?php
namespace App\Lib\SlimVC;

class Logger {
	public function __construct(){
		$this->log = array();
	}

	public function write( $message ){
		$this->log[] = $message;
	}

	public function flush(){
		return array_reduce($this->log, function($memo, $el){
			return $memo . '<br>' . $el;
		}, "");
	}
}