<?php

namespace App\Controllers;

use \App\Models\BooksModel as BooksModel;

class BooksController{
	public function __construct( $App, $params ){
		$this->App = $App;
		$this->BooksModel = new BooksModel();
		$this->render();
	}

	public function render(){
		return $this->App->render('posts.html',
			array(
				'posts' => $this->BooksModel,
				'type' => 'books'
			)
		);
	}
}