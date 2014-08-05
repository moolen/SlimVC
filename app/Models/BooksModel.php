<?php
namespace App\Models;

use \ArrayIterator;
use \IteratorAggregate;
use \App\Lib\SlimVC\PostModel;

class BooksModel implements \IteratorAggregate{

	protected $post;
	protected $posts;

	public function __construct($post = null){
		$this->post = new PostModel($post);
	}

	public function getAllBooks(){

		// fetch all posts
		$posts = \get_posts(array(
			'post_type' => 'books',
			'post_status' => 'publish',
			'posts_per_page' => -1
		));

		// return new PostModel Instance
		return array_map(function( $post ){
			return new PostModel( $post );
		}, $posts);
	}

	public function getSingleBook(){
		return $this->post;
	}


	public function getIterator(){
		return new \ArrayIterator( $this->posts );
	}

}