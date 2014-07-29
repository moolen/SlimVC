<?php

namespace App\Lib\SlimVC;

use \App\Lib\SlimVC\PostModel;
use \Slim\View as SlimView;

class Controller extends SlimView{
	
	public function __construct( $View ){
		global $post;
		$this->view = $View;
		$this->post = new \App\Lib\SlimVC\PostModel( $post );
	}
}