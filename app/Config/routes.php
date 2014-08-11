<?php

/**
 * SlimVC's routing-table
 */
return array(

	'explicit' => array(
		// sample with optional :bar param
		//array(
		//	'method'		=> 'GET',
		//	'path'			=> '/my-books/(:bar?)',
		//	'controller'	=> 'BooksController::'
		//),

		//array(
		//	// GET method is default
		//	'path'			=> '/baz/:bee',
		//	'controller'	=> 'PostsController::foo'
		//),

	),

	'conditional' => array(

		// page / page-template 
		//array(
		//	'page' => true,
		//	'page_template' => 'my-template',
		//	'controller' => 'PageController::myCustomTemplate'
		//),

		// Custom Post Type Archive
		//array(
		//	'archive' => true,
		//	'post_type' => 'books',
		//	'controller' => 'BooksController::archive'
		//),

		// Custom Post Type Single
		//array(
		//	'single' => true,
		//	'post_type' => 'books',
		//	'controller' => 'BooksController::single'
		//),

		// Home Page 
		//array(
		//	'home' => true,
		//	'controller' => 'PostsController::render'
		//),

		// you SHOULD always adda 4ß4 handler
		//array(
		//	'404' => true,
		//	'controller' => 'NotFoundController'
		//)

	)

);