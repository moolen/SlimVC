<?php

namespace App\Controllers;

use \Slim\Views\Twig as Twig;

class PageController{
	public function __construct( $App ){
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