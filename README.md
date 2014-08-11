# SlimVC
This is a lightweight MVC Abstraction Layer on top of Wordpress; SlimVC is not a Framework that wrapps the Wordpress API like [Themosis](http://www.themosis.com) does, it is a **set of tools** that lets you configure your Wordpress application's custom post types, taxonomies, image-sizes, sidebars, menus, page-templates and custom routes.

You will write better, maintainable and extensible code by using the MVC paradigm.
- no more PHP / HTML string concatenation
- no `archive-{$post-type}.php` and other templates
- No more mixing data-aggregation, business-logic and template code.

# Documentation
The Docs have their own repository [here](https://github.com/moolen/SlimVC-documentation).

# Features
- MVC Layer
- Unit Testing (PHPUnit)
- Twig templating engine
- Configuration engine
- Routing engine

# Requirements
- PHP 5.3
- Wordpress ~3.8

# Installation
- clone this repository into your `themes/` folder.
- install composer (https://getcomposer.org/download/) 
- run `composer install` inside `themes/<your-theme>/app/` directory.

## License

The SlimVC is released under the MIT public license.