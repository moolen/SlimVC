<?php

require 'app/vendor/autoload.php';

$App = new App\Lib\SlimVC\SlimVC();

// register post type
$App->registerPostType( 'Book', 'books' );

// register CT
$App->registerTaxonomy( 'BookCategories', 'book-categories' );

// add page Template
$App->addPageTemplate( 'Fresh Example Template', 'my-template' );

// conditional routes
$App->Router->is( 'home', 'PostsController' );
// $App->Router->is( 'page', 'PageController' );
//$App->Router->is( array( 'page' => true , 'page_template' => 'my-template' ), 'PageController' );
$App->Router->is(
	array(
		'page' => array('my-foo-slug', 'My Page Title'),
		'page_template' => 'my-template'
	), 'PageController'
);

$App->Router->is( '404', 'NotFoundController' );

// Explicit Routes.

// Anon function callback form
// $App->Router->get('/foo/', function($view, $params){
// 	echo "Hello from anon function";
// });

// Array w/ instance+method callback form 
//$App->Router->get('/foo/', array($instance, 'callMethod'));

// the following route supports:
// /books
// /books/
// /books/one
// /books/one/
// /books/one/two
// /books/one/two/
$App->Router->get( '/books(/?)(/:book(/?)(/:another(/?)?))', 'BooksController' );

// /api group
$App->Router->group('/api', function() use ($App){
	// /library group
	$App->Router->group('/library', function() use ($App){

		$App->Router->get('/books/:id(/:stuff?)', function($View, $params){
			$id = $params[0];
			$stuff = $params[1];
			echo 'book #' . $id;
		});

		$App->Router->post('/books/', function($View, $params){
			echo 'creating book...';
		});

		$App->Router->put('/books/:id', function($View, $params){
			$id = $params[0];
			echo 'editing book #' . $id;
		});
	});
});