<?php

namespace App\Controllers;

use \App\Models\PostsModel as PostsModel;
use \App\Lib\SlimVC\PostModel as PostModel;

class PostsController{
	public function __construct( $App ){
		$this->App = $App;
		$this->PostsModel = new PostsModel();
	}

	public function foo(){
		echo "hello from foo!";
	}

	public function render(){
		//$this->App->response->headers->set('CONTENT_TYPE', 'application/json');
		//$this->App->response->setBody('{"foo":"bar"}');
		$this->App->response->finalize();

		return $this->App->render('posts.html',
			array(
				'posts' => $this->PostsModel,
				'type' => 'posts'
			)
		);
	}
}