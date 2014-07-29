<?php

namespace App\Controllers;

use \App\Lib\SlimVC\Controller as BaseController;

class NotFoundController extends BaseController{
	public function __construct( $view ){
		parent::__construct( $view );
		$this->render();
	}

	public function render(){
		return $this->view->render('404.html');
	}
}