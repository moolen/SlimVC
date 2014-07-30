<?php

namespace App\Controllers;

use \App\Lib\SlimVC\Controller as BaseController;
use \App\Models\BooksModel as BooksModel;

class BooksController extends BaseController{
	public function __construct( $App, $params ){
		parent::__construct( $App );
		$this->BooksModel = new BooksModel();
		$this->render();
	}

	public function render(){
		return $this->App->render('posts.html',
			array(
				'posts' => $this->BooksModel
			)
		);
	}
}