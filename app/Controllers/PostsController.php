<?php

namespace App\Controllers;

use \App\Lib\SlimVC\Controller as BaseController;
use \App\Models\PostsModel as PostsModel;

class PostsController extends BaseController{
	public function __construct( $View ){
		parent::__construct( $View );
		$this->PostsModel = new PostsModel();
		$this->render();
	}

	public function render(){
		return $this->view->render('posts.html',
			array(
				'posts' => $this->PostsModel
			)
		);
	}
}