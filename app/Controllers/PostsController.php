<?php

namespace App\Controllers;

use \App\Models\PostsModel as PostsModel;
use \App\Lib\SlimVC\PostModel as PostModel;

class PostsController{
	public function __construct( $App ){
		$this->App = $App;
		$this->PostsModel = new PostsModel();
		$this->render();
	}

	public function render(){
		return $this->App->render('posts.html',
			array(
				'posts' => $this->PostsModel,
				'type' => 'posts'
			)
		);
	}
}