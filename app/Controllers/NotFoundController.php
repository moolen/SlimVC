<?php

namespace App\Controllers;

use \App\Lib\SlimVC\Controller as BaseController;

class NotFoundController extends BaseController{
	public function __construct( $App ){
		parent::__construct( $App );
		$this->render();
	}

	public function render(){
		return $this->App->render('404.html');
	}
}