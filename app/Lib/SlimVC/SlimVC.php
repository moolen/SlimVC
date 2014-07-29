<?php
namespace App\Lib\SlimVC;

use \Slim\Slim as libSlim;
use \Slim\Views\Twig as TwigView;
use \App\Lib\SlimVC\Router as Router;
use \App\Lib\SlimVC\Logger as Logger;

class SlimVC{

	/**
	 * holds our merged slimOptions
	 * @var [array]
	 */
	protected $slimOptions = null;

	/**
	 * holds a list of functions which will be called
	 * after slim is initialized
	 * @var array
	 */
	protected $initializerList = array();

	/**
	 * holds the post type registration argumets
	 * they will be called @do_action <init>
	 * @var array
	 */
	protected $postTypeList = array();

	/**
	 * holds the taxonomy registration arguments
	 * they will be called @do_cation <init>
	 * @var array
	 */
	protected $taxonomyList = array();

	/**
	 * holds our Router class
	 * @var [Router]
	 */
	public $Router = null;

	/**
	 * sets the slimOptions, registers the wp-core-callbacks
	 * 
	 * @param [array] $slimOptions [description]
	 * @uses  add_action [wordpress-core]
	 */
	public function __construct( array $slimOptions = array() ){

		// merge & save opts
		$this->slimOptions = array_merge(
			array(
				'view' => new TwigView(),
				'templates.path' => dirname(__FILE__) . '/../../Views',
				'debug' => true,
				'log.enabled' => true,
				'log.writer' => new Logger(),
				'log.level' => \Slim\Log::DEBUG
			),
			$slimOptions
		);

		$this->Router = new Router( new libSlim( $this->slimOptions ) );

		// add necessary action & filter callbacks
		add_action( 'template_redirect', array($this, 'onTemplateRedirectCallback') );
		add_action( 'init' , array($this, 'onInitCallback') );
	}

	/**
	 * calls the initializers 
	 * @return [void]
	 */
	protected function callInitializers(){
		foreach( $this->initializerList as $fn ){
			call_user_func($fn);
		}
	}

	/**
	 * registers all post types
	 * @return [void]
	 */
	protected function registerPostTypeList(){
		foreach( $this->postTypeList as $slug => $args ){
			register_post_type( $slug, $args );
		}
	}

	/**
	 * registers custom taxonomies
	 * @return [void]
	 */
	protected function registerTaxonomyList(){
		foreach( $this->taxonomyList as $slug => $args ){
			register_taxonomy($slug, $args);
		}
	}

	/**
	 * on do_action <template_redirect> callback
	 * this is the first action hook when conditional tags are available
	 * @return [void]
	 */
	public function onTemplateRedirectCallback(){
		$this->Router->setConditionalTags();
		$this->Router->assignRoutes();
		$this->callInitializers();
		$this->Router->run();
		exit;
	}

	/**
	 * registers custom post-types and custom taxonomies
	 * @do_action <init> callback
	 * @return [void]
	 */
	public function onInitCallback(){
		$this->registerPostTypeList();
		$this->registerTaxonomyList();
	}

	/**
	 * adds a callback to initializerList
	 * which will be called after Slim is initialized
	 * @param [function] $fn
	 */
	public function addInitializer( $fn ){
		$this->initializerList[] = $fn;
		return $this;
	}

	/**
	 * registers a Post type @wordpress
	 * called @do_action <init>
	 * @uses   register_post_type
	 * 
	 * @param  [type] $name
	 * @param  [type] $slug
	 * @param  array  $args
	 * @return SlimVC
	 */
	public function registerPostType( $name, $slug, array $args = array() ){
		$this->postTypeList[ $slug ] = array_merge(
			// defaults
			array(
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'rewrite' => array(
					'slug' => $slug
				),
				'labels' => array(
					'name' => $name
				)
			),
			// users overrides
			$args
		);

		return $this;
	}

	/**
	 * registers a custom Taxonomy
	 * called @do_action <init>
	 * @uses  register_taxonomy
	 * @param  [string] $name
	 * @param  [string] $slug
	 * @param  [array]  $args
	 * @return SlimVC
	 */
	public function registerTaxonomy( $name, $slug, array $args = array() ){
		$this->taxonomyList[ $slug ] = array_merge(
			//defaults
			array(
				'labels' => array(
					'name' => $name
				)
			),
			// user opts
			$args
		);

		return $this;
	}

}