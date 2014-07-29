# SlimVC
This is a MVC Abstraction Layer on top of Wordpress. This is a work-in-progress trial to fix it.
This is heavily inspired by http://www.themosis.com/ (check it out!)

# Features

- First-class support for AdvancedCustomFields plugin
- First-class support for WPML plugin
- WP best-practices
- OOP Wordpress API
- Templating Engine

## Explicit Routing

```PHP
// @functions.php

// create Instance
$App = new App\Lib\SlimVC\SlimVC();

// set the controller namespace
// this is actually the default
$App->setControllerNamespace('\\App\\Controllers\\');

//
// \App\Controllers\PageController is instantiated
// when <wp-url>/foo is requested 
// more on Controllers below
$App->Router->get('/foo', 'PageController');
$App->Router->get('/foo(/:bar?)', 'PostsController');

// matches
// /books
// /books/
// /books/one
// /books/one/
// /books/one/two
// /books/one/two/
$App->Router->get('/books(/?)(/:book(/?)(/:another(/?)?))', 'BooksController');
```
## Conditional Routing
Internally conditional tags are used for the following routing method. Currently there are not all Conditional Tags supported. the supported ones are: `home, front_page, blog_page, admin, single, page, (page_template), category, tag, tax, archive, search, singular, 404`.


```PHP
// @functions.php
// when the "home" page is requested  \App\Controllers\HomeController
// is constructed
$App->Router->is('home', 'HomeController');
$App->Router->is('page', 'PageController');
$App->Router->is('404', 'NotFoundController');
$App->Router->is(array('singular', 'page'), 'PageController');
```
## MVC Structure

Checkout a controller in `app/Controllers/`.

Sample from Above: A route matches /books(/:book(/:another))

```PHP
namespace App\Controllers;

use \App\Lib\SlimVC\Controller as BaseController;
use \App\Models\BooksModel as BooksModel;

class BooksController extends BaseController{

	// params holds (:book) and (:another) url params
	// $View is a \Slim\Slim instance
	public function __construct( $View, $params ){
		
		// construct the parent to set $View 
		// and global $post
		parent::__construct( $View );
		$this->BooksModel = new BooksModel();
		
		// dont forget to call render()
		$this->render();
	}
	
	// render has to return a <html> string
	public function render(){
		// 1st arg: the view path (relative to app/Views)
		// 2nd arg: the data to be injected into the view
		return $this->view->render('posts.html',
			array(
				'posts' => $this->BooksModel
			)
		);
	}
}

```

Checkout a model in `app/Models/`.

```PHP

namespace App\Models;

use \ArrayIterator;
use \IteratorAggregate;
use \App\Lib\SlimVC\PostModel;

// implement IteratorAggregate to iterate conveniently
// over the model inside the twig-view
class BooksModel implements \IteratorAggregate{

	protected $posts;

	public function __construct(){
		$this->posts = $this->getPosts();
	}

	public function getPosts(){

		// fetch all posts
		$posts = \get_posts(array(
			'post_type' => 'books',
			'post_status' => 'publish',
			'posts_per_page' => -1
		));

		// return new PostModel Instance per Post
		return array_map(function( $post ){
			return new PostModel( $post );
		}, $posts);
	}

	// Implement IteratorAggregate
	public function getIterator(){
		return new \ArrayIterator( $this->posts );
	}

}

```

## Templating Engine
Currently only Twig is supported, but per-se replaceable (https://github.com/codeguy/Slim-Views).
Views are located in `app/Views/`.

The Twig Documentation is over here: http://twig.sensiolabs.org/documentation.

## WPAL
Wordpress Abstraction Layer for `Custom Post Types`, `Custom Taxonomies`, and the ACF Plugin. More APIs soon.

```PHP

// register CPT
// signature: string $label, string $slug, optional array $args
$App->registerPostType('Book', 'books', $args);

// register CT
$App->registerTaxonomy('Store', 'stores', $args);

// add Page Template programatically
$App->addPageTemplate('Fresh Example Template', 'my-template');

```
The SlimVC Class exposes a Event-Driven Api to register Callbacks to all wordpress action hooks  `muplugins_loaded, plugins_loaded, setup_theme, after_setup_theme, init, wp_loaded, template_redirect`.

```PHP

$App->on('init', function(){
	// do init stuff
});

$App->on('setup_theme', function(){
	// do setup_theme stuff
});

```

# Requirements
- PHP 5.4
- Wordpress ~3.8


# Installation
clone this repository into your `themes/` folder.
install composer (https://getcomposer.org/download/) and run `composer install` inside `themes/<your-theme>/app/` directory.
Be sure to create a .htaccess.


# Todo

- ACF v5 API
- Wrap more WP APIs 
- Configuration / ENV API
- More options for SlimVC constructor