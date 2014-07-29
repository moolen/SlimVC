<?php

namespace App\Controllers;

use \App\Lib\SlimVC\Controller as BaseController;
use \App\Models\BooksModel as BooksModel;

class BooksController extends BaseController{
	public function __construct( $View, $params ){
		parent::__construct( $View );
		$this->BooksModel = new BooksModel();
		$this->render();
	}

	public function render(){
		return $this->view->render('posts.html',
			array(
				'posts' => $this->BooksModel
			)
		);
	}
}