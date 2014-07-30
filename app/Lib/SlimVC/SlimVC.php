<?php
namespace App\Lib\SlimVC;

use \Slim\Slim as libSlim;
use \Slim\Views\Twig as TwigView;
use \App\Lib\SlimVC\PageTemplate as PageTemplate;
use \App\Lib\SlimVC\Router as Router;
use \App\Lib\SlimVC\Logger as Logger;

class SlimVC{

	/**
	 * PHP 5.4 Trait 
	 * inherits Methods like:
	 * 
	 * .on('event', $foo, $bar)
	 * .once('event', $foo, $bar)
	 * .off('event', $callable)
	 * .emit('event', $foo, $bar)
	 * .addListener('event', $callable)
	 * .removeListener('event', $callable)
	 */
	use \Nekoo\EventEmitter;

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
	 * holds our Template class
	 * @var [PageTemplate]
	 */
	protected $PageTemplate = null;

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
				'log.enabled' => false,
				'log.writer' => new Logger(),
				'log.level' => \Slim\Log::DEBUG
			),
			$slimOptions
		);

		// instantiate Router 
		$this->Router = new Router( new libSlim( $this->slimOptions ) );

		$this->PageTemplate = new PageTemplate();

		// add necessary action & filter callbacks
		add_action( 'muplugins_loaded', array($this, 'onMuPluginsLoaded') );		
		add_action( 'plugins_loaded', array($this, 'onPluginsLoaded') );		
		add_action( 'setup_theme', array($this, 'onSetupTheme') );		
		add_action( 'after_setup_theme', array($this, 'onAfterSetupTheme') );		
		add_action( 'init' , array($this, 'onInit') );
		add_action( 'wp_loaded', array($this, 'onWpLoaded') );
		add_action( 'template_redirect', array($this, 'onTemplateRedirect') );
		
	}

	/**
	 * calls the initializers callbacks
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
	 * event callback for muplugins_loaded
	 * @return [void]
	 */
	public function onMuPluginsLoaded(){
		$this->emit('muplugins_loaded');
	}

	/**
	 * event callback for plugins_loaded
	 * @return [void]
	 */
	public function onPluginsLoaded(){
		$this->emit('plugins_loaded');
	}

	/**
	 * event callback for setup_theme
	 * @return [void]
	 */
	public function onSetupTheme(){
		$this->emit('setup_theme');
	}

	/**
	 * event callback for after_setup_theme
	 * @return [void]
	 */
	public function onAfterSetupTheme(){
		$this->emit('after_setup_theme');
	}

	/**
	 * registers custom post-types and custom taxonomies
	 * event callback for init
	 * @return [void]
	 */
	public function onInit(){
		$this->registerPostTypeList();
		$this->registerTaxonomyList();
		$this->emit('init');
	}

	/**
	 * event callback for wp_loaded
	 * @return [void]
	 */
	public function onWpLoaded(){
		$this->emit('wp_loaded');
	}

	/**
	 * event callback for template_redirect
	 * this is the first action hook when conditional tags are available
	 * @return [void]
	 */
	public function onTemplateRedirect(){
		$this->Router->setConditionalTags();
		$this->Router->assignRoutes();
		$this->callInitializers();
		$this->Router->run();
		$this->emit('template_redirect');
		exit;
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

	public function addPageTemplate($name, $slug){
		$this->PageTemplate->addPageTemplate($name, $slug);
		return $this;
	}

	/**
	 * sets the controller Namespace
	 * @param [string] $ns
	 * @return SlimVC
	 */
	public function setControllerNamespace( $ns ){
		$this->Router->setControllerNamespace($ns);
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