<?php

require 'app/vendor/autoload.php';

$App = App\Lib\SlimVC\SlimVC::getInstance();

// conditional routes
$App->Router->is( 'home', 'PostsController' );

$App->Router->is(
	array(
		'page' => array('my-foo-slug', 'My Page Title'),
		'page_template' => 'my-template'
	), 'PageController'
);

$App->on('init', function(){
	echo "init called";
});

$App->Router->is( '404', 'NotFoundController' );

$App->Router->get( '/books(/?)(/:book(/?)(/:another(/?)?))', 'BooksController' );

// /api group
$App->Router->group('/api', function() use ($App){
	// /library group
	$App->Router->group('/library', function() use ($App){

		$App->Router->get('/books/:id', function($View, $params){
			$id = $params[0];
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