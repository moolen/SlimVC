# SlimVC
This is a MVC Abstraction Layer on top of Wordpress. Seriously, the wordpress API sucks. This is a work-in-progress trial to fix it.
This is heavily inspired by http://www.themosis.com/ (check it out!)



# Features

## Explicit Routing

```PHP
// create Instance
$App = new App\Lib\SlimVC\SlimVC();

$App->Router->get('/foo', 'PageController');
$App->Router->get('/foo(/:bar?)', 'PostsController');

// matches
// /books
// /books/
// /books/one
// /books/one/
// /books/one/two
// /books/one/two/
$App->Router->get('/books(/?)(/:book(/?)(/:another(/?)?))', BooksController);


```
## Conditional Routing
```PHP
$App = $App->Router->is('home', 'HomeController');
$App = $App->Router->is('page', 'PageController');
$App = $App->Router->is('404', 'NotFoundController');
```
## MVC Structure

Checkout a controller in `app/Controllers/`.
Checkout a models in `app/Models/`.
Checkout a Views in `app/Views/`.

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
- Configuration API