<?php

namespace App\Lib\SlimVC;

use \App\Lib\SlimVC\PostModel;
use \Slim\View as SlimView;

class Controller extends SlimView{
	
	public function __construct( $App ){
		global $post;
		$this->App = $App;
		$this->post = new \App\Lib\SlimVC\PostModel( $post );
	}
}