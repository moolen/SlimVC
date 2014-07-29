<?php

namespace App\Lib\SlimVC;

class PostModel{

	public function __construct( $post ){
		$this->post = $post;
	}

	public function title(){
		return $this->post->post_title;
	}

	public function id(){
		return $this->post->ID;
	}

	public function date(){
		return $this->post->post_date;
	}

	public function content(){
		return $this->post->post_content;
	}

}