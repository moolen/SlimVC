<?php
namespace App\Lib\SlimVC;

class Logger {
	public function __construct( $resource = null ){
		$this->resource = $resource;
	}

	public function write( $message ){
		echo $message . "<br>";
	}
}