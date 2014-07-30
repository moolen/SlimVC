<?php

namespace App\Controllers;

use \App\Lib\SlimVC\Controller as BaseController;
use \Slim\Views\Twig as Twig;

class PageController extends BaseController{
	public function __construct( $App ){
		parent::__construct( $App );
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