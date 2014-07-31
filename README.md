# SlimVC
This is a MVC Abstraction Layer on top of Wordpress. This is a work-in-progress trial to fix it.
This is heavily inspired by http://www.themosis.com/ (check it out!)

## Explicit Routing

Basic HTTP Methods are supported:
- `GET`
- `POST`
- `PUT`
- `DELETE`
- `PATCH`

The callback/constructor/classMethod will recieve 2 Arguments: first, a `\Slim\Slim` instance (aka $App), second a $params array which holds the optional routing params (like /foo/:bar/:id)

More Documentation about `\Slim\Slim`:

- http://docs.slimframework.com/#Request
- http://docs.slimframework.com/#Response
- http://docs.slimframework.com/#HTTP-Caching

```PHP
// @functions.php

// create Instance
$App = App\Lib\SlimVC\SlimVC::getInstance();

//
// \App\Controllers\PageController is instantiated
// when <wp-url>/foo is requested 
// more on Controllers below
$App->Router->get('/foo', 'PageController');
$App->Router->post('/foo/:id', 'PostsController');
$App->Router->put('/foo/:id)', 'PostsController');
$App->Router->delete('/foo/:id)', 'PostsController');

// also anonymous functions are supported.
$App->Router->get('/fohk(/:yeah?)', function($App, $params){
	// do stuff.
});

// also array form
$App->Router->get('/fohk(/:yeah?)', ($myInstance, 'myMethod'));

// matches
// /books
// /books/
// /books/one
// /books/one/
// /books/one/two
// /books/one/two/
$App->Router->get('/books(/?)(/:book(/?)(/:another(/?)?))', 'BooksController');
```
### Route Groups

SlimVC API exposes \Slim\Slim Route Group API with slightly different conventions. The First argument of a routing callback is always a \Slim\Slim object (aka $App), the second is an array of routing params.

http://docs.slimframework.com/#Route-Groups

```PHP
// /api group
$App->Router->group('/api', function() use ($App){
	// /library group
	$App->Router->group('/library', function() use ($App){

		$App->Router->get('/books/:id(/:stuff?)', function($App, $params){
			$id = $params[0];
			$stuff = $params[1];
			echo 'book #' . $id;
		});

		$App->Router->post('/books/', function($App, $params){
			echo 'creating book...';
		});

		$App->Router->put('/books/:id', function($App, $params){
			$id = $params[0];
			echo 'editing book #' . $id;
		});
	});
});
```

## Conditional Routing
Internally conditional tags are used for the following routing method. Currently there are not all Conditional Tags supported. the supported ones are: `home, front_page, blog_page, admin, single, page, page_template, category, tag, tax, archive, search, singular, 404`.

```PHP
// @functions.php
// when the "home" page is requested  \App\Controllers\HomeController
// is constructed
$App->Router->is('home', 'HomeController');
$App->Router->is('page', 'PageController');
$App->Router->is('404', 'NotFoundController');

// must be singular and page
$App->Router->is(array('singular', 'page'), 'PageController');

```

You can also pass arguments to the conditional tags using an array as first argument. Above would be equal `array('home' => true)`.

```PHP
// this matches :
// (page with id=5 OR slug='my-foo-slug' OR title='My Page Title')
// AND
// page_template='my-template'
$App->Router->is(
	array(
		'page' => array(5, 'my-foo-slug', 'My Page Title'),
		'page_template' => 'my-template'
	), 'PageController'
);
```

## MVC Structure

### Controller

Checkout a controller in `app/Controllers/`.

How you implement the Controller is up to you. This is a demonstration of how it could be implemented.
The constructor always gets 2 arguments: 

- 1. $App instance
- 2. $params array containing optional route parameter

with the $App instance you can render a view with:
`$App->render('path/to/view.ext', array('inject' => 'this variable into view'))`

```PHP
namespace App\Controllers;

use \App\Models\PostsModel as PostsModel;

class PostsController{
	public function __construct( $App, $params ){
		$this->App = $App;
		$this->params = $params;
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

```

### Models

Checkout a model in `app/Models/`.
This is also a example of how you could implement Models.

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

### PostModel 

SlimVC comes with a common abstraction of the WP_Post Object (`\App\Lib\SlimVC\PostModel`).
This provides you a neat interface to the WP_Post Object:

- $PostModel->getTitle($strlen, $raw, $closure, $ellipsis)
- $PostModel->getPost()
- $PostModel->getContent($strlen, $raw, $ellipsis)
- $PostModel->getFeaturedImageUrl()
- $PostModel->getCustomField($name)
- $PostModel->getDate($format)
- $PostModel->getSubPosts( $args )
- $PostModel->hasSubPosts()
- $PostModel->isChildOf( $parent )
- $PostModel->isInCategory($slug)
- PostModel::get_a_Title($post, $strlen, $raw, $ellipsis)
- PostModel::get_a_CustomField($post, $name)
- PostModel::get_a_FeaturedImageUrl($post)
- PostModel::get_a_Date($post, $format)
- PostModel::get_the_CustomFields($post)
- PostModel::get_the_subPosts($post, $args)
- PostModel::get_a_PostBySlug($slug, $wpPostObj)
- PostModel::has_PostSubPosts($post)
- PostModel::is_PostChildOf($post, $parent)
- PostModel::is_PostInCategory($post, $slug)

(detailed documentation coming soon)

### Views / Templating Engine
Currently only Twig is supported, but per-se replaceable (https://github.com/codeguy/Slim-Views).
Views are located in `app/Views/`.

The Twig Documentation is over here: http://twig.sensiolabs.org/documentation.

## Configuration
All Configuration is done in `app/Config` Folder. This include the registration of custom post types, custom taxonomies, menus, sidebars, image size definition and post-templates.

Image size definition inside `images.php`:
```PHP
return array(
	'my_size' => array(200, 256),
	// size-name                x    y  crop-mode
	'my_size_cropped' => array(200, 256, true),
);
```

Menu configuration in `menus.php`:
```PHP
return array(
	// slug         -> Pretty name   
	'header-nav'	=> 'Header navigation',
	'footer-nav'	=> 'Footer navigation'
);
```

Post type definition in `postType.php`:
```PHP
return array(
	// CPT name -> array
	'books' => array(
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'book' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
		'labels'             => array(
			'name'               => _x( 'Books', 'post type general name', 'my-app' ),
			'singular_name'      => _x( 'Book', 'post type singular name', 'my-app' ),
			'menu_name'          => _x( 'Books', 'admin menu', 'my-app' ),
			'name_admin_bar'     => _x( 'Book', 'add new on admin bar', 'my-app' ),
			'add_new'            => _x( 'Add New', 'book', 'my-app' ),
			'add_new_item'       => __( 'Add New Book', 'my-app' ),
			'new_item'           => __( 'New Book', 'my-app' ),
			'edit_item'          => __( 'Edit Book', 'my-app' ),
			'view_item'          => __( 'View Book', 'my-app' ),
			'all_items'          => __( 'All Books', 'my-app' ),
			'search_items'       => __( 'Search Books', 'my-app' ),
			'parent_item_colon'  => __( 'Parent Books:', 'my-app' ),
			'not_found'          => __( 'No books found.', 'my-app' ),
			'not_found_in_trash' => __( 'No books found in Trash.', 'my-app' )
		),
	)
);
```

Sidebar definition in `sidebars.php`:
```PHP
return array(
	array(
		'name'			=> 'Blog',
		'id'			=> 'blog-sidebar',
		'description'	=> 'Blog sidebar.',
		'before_widget'	=> '<div class="sidebar-widget"><div class="sidebar-widget_content">',
		'after_widget'	=> '</div></div>',
		'before_title'	=> '<h4>',
		'after_title'	=> '</h4>'
	)
);
```
Custom taxonomies in `taxonomies.php`:

```PHP
return array(
	'genre' => array(
		// string or array of post type(s)
		'postType' => 'books',
		'args' => array(
			'labels'            => array(
				'name'              => _x( 'Genres', 'taxonomy general name' ),
				'singular_name'     => _x( 'Genre', 'taxonomy singular name' ),
				'search_items'      => __( 'Search Genres' ),
				'all_items'         => __( 'All Genres' ),
				'parent_item'       => __( 'Parent Genre' ),
				'parent_item_colon' => __( 'Parent Genre:' ),
				'edit_item'         => __( 'Edit Genre' ),
				'update_item'       => __( 'Update Genre' ),
				'add_new_item'      => __( 'Add New Genre' ),
				'new_item_name'     => __( 'New Genre Name' ),
				'menu_name'         => __( 'Genre' ),
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'genre' ),
		)
	)
);
```

And also page-templates can be defined in `templates.php`:
```PHP
return array(
	// slug       ->  pretty name
	'my-template' => 'Fresh Example Template'
);
``` 
And last but not least we have a application configuration inside `application.php`:
```PHP
return array(
	// global debug mode
	'debug' => true,
	'namespace.controller' => '\\App\\Conrollers\\',
	'slim' => array(
		// env vars
		'log.enabled' => true,
		'log.writer' => new \App\Lib\SlimVC\Logger(),
		'log.level' => \Slim\Log::DEBUG,

		// view & templating
		'view' => new \Slim\Views\Twig(),
		'templates.path' => dirname(__FILE__) . '/../Views',
	)
);
```
## ACF
With version 5 ACF field definitions are saved automatically as json inside the theme directory. With SlimVC those files are saved within `app/Config/acf`.


## Event API

The SlimVC Class exposes a eventdriven API to register callbacks to all Wordpress action hooks  `muplugins_loaded, plugins_loaded, setup_theme, after_setup_theme, init, wp_loaded, template_redirect`.

```PHP

$App->Event->on('init', function(){
	// do init stuff
});

// also array form is supported
$App->Event->on('setup_theme', array('myClass', 'someMethod'));

$App->Event->on('wp_load', function(){
	// do wp_load stuff
});

```

# Requirements
- PHP 5.3
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