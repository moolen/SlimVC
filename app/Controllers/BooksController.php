<?php

namespace App\Controllers;

use \App\Models\BooksModel as BooksModel;

class BooksController{

	public function __construct( $App, $params ){
		$this->App = $App;
		$this->BooksModel = new BooksModel($App->post);
	}
 
 	/**
 	 * handles the archive page
 	 */
	public function archive(){
		$this->App->render('posts.html', array(
			'posts' => $this->BooksModel->getAllBooks(),
			'type' => 'books'
		));
	}

	/**
	 * handles a single book 
	 */
	public function single(){
		$this->App->render('single.html', array(
			'post' => $this->BooksModel->getSingleBook()
		));
	}
}