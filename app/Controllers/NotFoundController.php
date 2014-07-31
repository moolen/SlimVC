<?php

namespace App\Controllers;


class NotFoundController{
	public function __construct( $App ){
		$this->App = $App;
		$this->render();
	}

	public function render(){
		return $this->App->render('404.html');
	}
}