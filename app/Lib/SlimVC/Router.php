<?php
namespace App\Lib\SlimVC;

use \Slim\Views\TwigExtension as TwigExtension;
use \App\Lib\SlimVC\Logger;

class Router{

	/**
	 * default Controller namespace
	 * @var string
	 */
	protected $controllerNamespace = '\\App\\Controllers\\';

	/**
	 * default method seperator for route configuration
	 * @var string
	 */
	protected $methodSeperator = '::';

	/**
	 * holds our Slim instance
	 * @var [\Slim\Slim]
	 */
	protected $Slim = null;

	/**
	 * holds our routing configuration; extends from SlimVC $parent 
	 * @var array
	 */
	protected $routingConfiguration = array();

	/**
	 * logger instance
	 * @var [Object]
	 */
	public $Logger = null;

	/**
	 * LogLevel
	 * @var integer
	 */
	public $logLevel = 0;

	/**
	 * enable Log mode?
	 * @var boolean
	 */
	public $enableLogging = false;

	/**
	 * holds our conditional tags
	 * will be set after template_redirect
	 * @var array
	 */
	protected $conditionalTags = array(); 

	/**
	 * holds our conditional routes
	 * @var array
	 */
	protected $conditionalRoutes = array();

	/**
	 * defualt: explicit route conf
	 * @var array
	 */
	protected $defaultExplicitRoute = array(
		'method' => 'GET'
	);

	/**
	 * default: conditional route conf
	 * @var array
	 */
	protected $defaultConditionalRoute = array();

	public function __construct( $parent ){
		$this->parent = $parent;
		$this->Slim = $parent->Slim;
		$this->Logger = new Logger();

		// inherit loglevel from Slim instance
		$this->logLevel = $this->Slim->log->getLevel();
		$this->enableLogging = $this->parent->applicationConfiguration['log.enabled'];
		$this->debugMode = $this->parent->applicationConfiguration['debug'];
		$this->methodSeperator = $this->parent->applicationConfiguration['method.seperator'];
		$this->routeConfiguration = $this->parent->routeConfiguration;

		$this->Slim->view()->parserOptions = array('debug' => $this->debugMode);
		$this->Slim->view()->parserExtensions = new TwigExtension();

		$this->applyRouteConfiguration();
	}

	/**
	 * calls a slim routing method
	 * @param  [string] $method
	 * @param  [string] $path
	 * @param  [string] $controller
	 * @return [void]
	 */
	protected function callSlimApi( $method, $path, $controller ){
		$self = $this;
		
		// e.g. $slim->get($path, $callback); the $callback calls the defined controller.
		call_user_func( array( $this->Slim, $method ), $path, function() use (&$self, &$controller){

			// params are optional routing args
			$params = func_get_args();

			// call controller
			$self->callController($controller, $params);
		});
	}

	/**
	 * applies the route configuration to the Router (set by ConfManager)
	 * @uses  SlimVC->routeConfiguration
	 * @return [type] [description]
	 */
	protected function applyRouteConfiguration(){

		$explicits = $this->routeConfiguration['explicit'];
		$conditionals = $this->routeConfiguration['conditional'];

		if( 8 <= $this->logLevel && $this->enableLogging ){
			$this->Logger->write('configuring explicit routes:');
		}

		// register explicit route configuration
		foreach( $explicits as $conf ){
			$merge = array_merge($this->defaultExplicitRoute, $conf);

			// check values
			if( is_string($merge['method']) && isset($merge['path']) && isset($merge['controller']) ){
				
				if( 8 <= $this->logLevel && $this->enableLogging ){
					$this->Logger->write('adding ' . $merge ['method'] . '(' . $merge['path'] . ')' );
				}

				// call slim api with args
				$this->callSlimApi(
					strtolower($merge['method']),
					$merge['path'],
					$merge['controller']
				);
			}else{
				throw new \Exception("Explicit Route-Configuration error: method, route & controller has to be set.");
			}
		}

		if( 8 <= $this->logLevel && $this->enableLogging ){
			$this->Logger->write('configuring conditional routes:');
		}

		// register conditional routes
		foreach( $conditionals as $conf ){
			$controller = $conf['controller'];
			unset($conf['controller']);

			// check for controller existence, throw otherwise
			if( isset($controller) ){

				if( 8 <= $this->logLevel && $this->enableLogging ){
					$this->Logger->write('adding conditional: ' . var_export($conf, true) . ' for '  .$controller );
				}

				// we have to translate true to "true"
				// (json_encode would do true => 1)
				// otherwise we would conflict with Post IDs
				foreach( $conf as $key => $value ){
					if( !is_int( $key ) && true === $value ){
						$conf[$key] = 'true';
					}
				}

				// json_encoded $conf is our key
				$key = json_encode($conf);
				$this->conditionalRoutes[$key] = $controller;
			}else{
				throw new \Exception("Conditional Route-Configuration: No Controller set.");
			}
		}
	}

	/**
	 * sets internal conditional tags array
	 * @reference http://codex.wordpress.org/Conditional_Tags
	 * @uses  wp-conditional-tags-functions
	 * @return array
	 */
	protected function getConditionalTags(){
		return array(
			'home' => \is_home(),
			'front_page' => \is_front_page(),
			'blog_page' => \is_home() && is_front_page(),
			'admin' => \is_admin(),
			'single' => \is_single(),
			'page' => \is_page(),
			'page_template' => \is_page_template(),
			'category' => \is_category(),
			'tag' => \is_tag(),
			'tax' => \is_tax(),
			'archive' => \is_archive(),
			'search' => \is_search(),
			'singular' => \is_singular(),
			'404' => \is_404(),
			'post_type_archive' => \is_post_type_archive(),

			// the following options default to true
			// they will be checked in @matchConditionalRoute
			'post_type' => true,
		);
	}

	/**
	 * called by slim if an error occures.
	 * @return [type] [description]
	 */
	public function errorHandler($str, $foo){
		//$args = ;
		//var_dump(func_get_args());
		//echo "errr.";
	}

	/**
	 * checks for matching conditional tags.
	 * if something matches it runs the controller.
	 * returns true if route was found otherwise false.
	 * @return [boolean]
	 */
	public function runConditionalRoutes(){
		$routeMatches = false;
		$routeController = false;
		$conditions = array();
		global $post;
		$this->Slim->post = $post;

		foreach( $this->conditionalRoutes as $conditionKey => $controller ){

			// if key is no json use directly
			if( null === $conditions = (array) json_decode( $conditionKey ) ){
				$conditions = $conditionKey;
			}

			// logging
			if( 8 <= $this->logLevel && $this->enableLogging ){
				$this->Logger->write('checking conditional match for: '. $conditionKey);
			}

			if( $this->matchConditionalRoute( $conditions ) ){
				$routeMatches = true;
				$routeController = $controller;

				// logging
				if( 8 <= $this->logLevel && $this->enableLogging ){
					$this->Logger->write('matched');
				}

				break;
			}else{

				// logging
				if( 8 <= $this->logLevel && $this->enableLogging ){
					$this->Logger->write('no match found.');
				}

			}
		}

		if( $routeMatches ){
			$this->callController($controller);
		}

		return $routeMatches;
	}

	/**
	 * checks if an array of condition matches to the 
	 * conditionalTags of this request.
	 * returns true if a match was found
	 * @param  array  $conditions
	 * @return [boolean]
	 */
	protected function matchConditionalRoute( array $conditions ){

		$post = $this->Slim->WP_Post;
		$matches = 0;
		$count = count($conditions);

		foreach( $conditions as $conditionKey => $conditionValue ){

			// additional argument as associative array submitted
			// like page_template => 'my-template'
			if( 'true' !== $conditionValue && true !== $conditionValue){
				$fn = 'is_' . $conditionKey;
				if( function_exists($fn) ){
					$conditionalArguments = call_user_func( $fn, $conditionValue );
				}else{
					$matched = $this->matchPostAttribute( $conditionKey, $conditionValue );
					$conditionalArguments = true;
				}				
			}else{
				$conditionalArguments = true;
			}

			if( true === $this->conditionalTags[ $conditionKey ] && $conditionalArguments ){
				$matches++;
			}
		}

		if( $matches === $count ){
			return true;
		}
		return false;
	}

	/**
	 * matches a $attr with $val on the global $post object
	 * @param  [string] $attr
	 * @param  [string] $val
	 * @return [bool]
	 */
	protected function matchPostAttribute($attr, $val){
		if( is_object( $this->Slim->WP_Post) ){
			if( $this->Slim->WP_Post->$attr && $this->Slim->WP_Post->$attr === $val ){
				return true;
			}
		}
		return false;
	}

	/**
	 * calls $controller with $controllerNamespace
	 * also passes $params for optional arguments in route 
	 * e.g. /foo( /:param1 ( /:param2 ) )
	 * param1 & param2 is optional
	 * @param  [string] $controller
	 * @param  [array] $params
	 * @return [void]
	 */
	public function callController( $controller, array $params=array() ){
		// call controller name
		// with $this->baseNamespace
		if( is_string( $controller ) ){

			$controller = explode($this->methodSeperator, $controller);
			$baseNamespace = $this->controllerNamespace;
			$class = $baseNamespace . $controller[0];
			$method = $controller[1];

			if( class_exists($class) ){
				
				$reflection = new \ReflectionClass( $class );
				
				$instance = $reflection->newInstanceArgs(array(
					$this->Slim,
					$params
				));

				if( is_string($method) ){
					if( method_exists($instance, $method) ){
						$instance->$method();
					}else{
						throw new \Exception("Class does not have method " . $method );
					}
				}
			}else{
				throw new \Exception("Class does not exist: " . $class);
			}
			
		// otherwise check if a callable is submitted
		}elseif( is_callable( $controller ) ){
			call_user_func( $controller, $this->Slim, $params );
		// else THROW err. since the controller is not valid.
		}else{
			var_dump($controller, $params);
			throw new Error("controller is neither string nor callable.");
		}

		
	}

	/**
	 * runs the routing engine.
	 * first: checks if conditional tags have been found.
	 * second: run Slim routes.
	 * @return [type] [description]
	 */
	public function run(){
		$this->Slim->run();
	}

	/**
	 * sets the Slim instance
	 * @param SlimSlim $Slim
	 */
	public function setSlimInstance( \Slim\Slim $Slim){
		$this->Slim = $Slim;
		return $this;
	}

	/**
	 * returns the Slim instance
	 * @return [Slim]
	 */
	public function getSlimInstance(){
		return $this->Slim;
	}

	/**
	 * assigns routes to Slim instance;
	 * explicit routes are preferred;
	 * conditional routes are run if NO explicit routes are found
	 * @return [type] [description]
	 */
	public function assignRoutes(){

		$self = $this;

		// explicit routes will be called BEFORE this one
		// use default Route
		// which handles the conditional Logic
		$this->Slim->get('/.*?', function() use (&$self){
			if( 8 <= $self->logLevel && $self->enableLogging ){
				$self->Logger->write('checking conditional routes...');
			}
			if( false === $self->runConditionalRoutes() ){
				$self->Logger->write( "no routes found." );
			}
		});
	}

	/**
	 * sets internal Conditional tags
	 */
	public function setConditionalTags(){
		$this->conditionalTags = $this->getConditionalTags();
	}

	/**
	 * adds Middleware to Slim application
	 * @param [object] $middleware
	 */
	public function addMiddleware( $middleware ){
		$this->Slim->add( $middleware );
	}

}