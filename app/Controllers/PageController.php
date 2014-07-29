<?php

namespace App\Controllers;

use \App\Lib\SlimVC\Controller as BaseController;
use \Slim\Views\Twig as Twig;

class PageController extends BaseController{
	public function __construct( $View ){
		parent::__construct( $View );
		$this->render();
	}

	public function render(){
		return $this->view->render('page.html',
			array(
				'post' => $this->post
			)
		);
	}
}