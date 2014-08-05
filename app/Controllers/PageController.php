<?php

namespace App\Controllers;

use \Slim\Views\Twig as Twig;

class PageController{
	public function __construct( $App ){
		global $post;
		$this->post = new \App\Lib\SlimVC\PostModel( $post );
		$this->App = $App;
		$this->render();
	}

	public function render(){
		return $this->App->render('page.html',
			array(
				'post' => $this->post
			)
		);
	}
}