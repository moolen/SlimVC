# SlimVC
This is a MVC Abstraction Layer on top of Wordpress. Seriously, the wordpress API sucks. This is a work-in-progress trial to fix it.
This is heavily inspired by http://www.themosis.com/ (check it out!)



# Features

## Explicit Routing

```PHP
// create Instance
$App = new App\Lib\SlimVC\SlimVC();

// set the controller namespace
$App->setControllerNamespace('\\App\\Controllers\\')

//
// \App\Controllers\PageController will be instantiated
// when <wp-url>/foo will be requested 
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
```PHP
// when the "home" page is requested  \App\Controllers\HomeController will be instantiated
$App = $App->Router->is('home', 'HomeController');
$App = $App->Router->is('page', 'PageController');
$App = $App->Router->is('404', 'NotFoundController');
```
## MVC Structure

Checkout a controller in `app/Controllers/`.

Sample from Above:
$App->Router->get('/books(/?)(/:book(/?)(/:another(/?)?))', 'BooksController');
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

// implement IteratorAggregate to iterate conveniently over the model inside the twig-view
// like for ( post in posts ){ //do stuff }
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

		// return new PostModel Instance
		return array_map(function( $post ){
			return new PostModel( $post );
		}, $posts);
	}

	public function getIterator(){
		return new \ArrayIterator( $this->posts );
	}

}

```

Checkout the Views in `app/Views/`.

## Twig as Templating Engine
currently only Twig is supported, but per-se replaceable. (https://github.com/codeguy/Slim-Views)

## WPAL
Wordpress Abstraction Layer for Custom Post Types, Custom Taxonomies, and the ACF Plugin.

# Installation
clone this repository into your `themes/` folder.
install composer (https://getcomposer.org/download/) and run `composer install` inside `themes/<your-theme>/app/` directory.
Be sure to create a .htaccess.


# Todo

- ACF v5 API
- Configuration / ENV API
- More options for SlimVC constructor