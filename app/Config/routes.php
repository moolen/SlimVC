<?php

/**
 * SlimVC's routing-table
 */
return array(

	'explicit' => array(

		array(
			'method'		=> 'GET',
			'path'			=> '/my-books/(:bar?)',
			'controller'	=> 'BooksController'
		),

		array(
			// GET method is default
			'path'			=> '/baz/:bee',
			'controller'	=> 'PostsController:foo'
		),

	),

	'conditional' => array(

		array(
			'page' => true,
			//'page_template' => 'my-template',
			'controller' => 'PageController'
		),

		array(
			'archive' => true,
			'post_type' => 'books',
			'controller' => 'BooksController:archive'
		),

		array(
			'single' => true,
			'post_type' => 'books',
			'controller' => 'BooksController:single'
		),

		array(
			'home' => true,
			'controller' => 'PostsController:render'
		),

		array(
			'404' => true,
			'controller' => 'NotFoundController'
		)

	)

);