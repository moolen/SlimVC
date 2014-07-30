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

// explicit routes
// $App->Router->get('/foo/', 'PageController');

// the following route supports:
// /books
// /books/
// /books/one
// /books/one/
// /books/one/two
// /books/one/two/
$App->Router->get( '/books(/?)(/:book(/?)(/:another(/?)?))', 'BooksController' );