<?php
namespace App\Lib\SlimVC;

use \Slim\Views\TwigExtension as TwigExtension;
use \App\Lib\SlimVC\Logger;

class Router{

	// opts & instances
	protected $controllerNamespace = '\\App\\Controllers\\';
	protected $Slim = null;
	protected $options = array();
	protected $Logger = null;
	protected $logLevel = 0;
	protected $enableLogging = false;

	// routing vars
	protected $explicitRoutes = array();
	protected $conditionalTags = array(); 
	protected $conditionalRoutes = array();

	public function __construct( $parent ){
		$this->parent = $parent;
		$this->Slim = $parent->Slim;
		$this->Logger = new Logger();

		// inherit loglevel from Slim instance
		$this->logLevel = $this->Slim->log->getLevel();
		$this->enableLogging = $this->parent->applicationConfiguration['debug'];

		$this->Slim->view()->parserOptions = array('debug' => true);
		$this->Slim->view()->parserExtensions = new TwigExtension();
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
		var_dump($method, $path);
		if( 8 <= $this->logLevel && $this->enableLogging ){
			$ctrl = $controller;
			if( is_callable($controller) ){
				$ctrl = 'Closure';
			}
			$this->Logger->write('adding route: ' . $method . '('.$path.') :: ' . $ctrl);
		}
		
		// e.g. $slim->get($path, $callback); the $callback calls the defined controller.
		call_user_func( array( $this->Slim, $method ), $path, function() use (&$self, &$controller){

			// params are optional routing args
			$params = func_get_args();

			// call controller
			$self->callController($controller, $params);
		});
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
			'404' => \is_404()
		);
	}

	/**
	 * checks for matching conditional tags.
	 * if something matches it runs the controller.
	 * returns true if route was found otherwise false.
	 * @return [boolean]
	 */
	protected function runConditionalRoutes(){
		$routeMatches = false;
		$routeController = false;
		$conditions = array();

		foreach( $this->conditionalRoutes as $conditionKey => $controller ){

			// 
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

		$matches = 0;
		$count = count($conditions);

		foreach( $conditions as $conditionKey => $conditionValue ){

			// additional argument as associative array submitted
			// like page_template => 'my-template'
			if( 'true' !== $conditionValue && true !== $conditionValue){
				$fn = 'is_' . $conditionKey;
				$conditionalArguments = call_user_func($fn, $conditionValue);
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
	 * calls $controller with $controllerNamespace
	 * also passes $params for optional arguments in route 
	 * e.g. /foo( /:param1 ( /:param2 ) )
	 * param1 & param2 is optional
	 * @param  [string] $controller
	 * @param  [array] $params
	 * @return [void]
	 */
	protected function callController( $controller, array $params=array() ){

		// call controller name
		// with $this->baseNamespace
		if( is_string( $controller ) ){
			$baseNamespace = $this->controllerNamespace;
			$class = $baseNamespace . $controller;
			if( class_exists($class) ){
				$reflection = new \ReflectionClass( $class );
				$instance = $reflection->newInstanceArgs(array(
					$this->Slim,
					$params
				));
			}else{
				var_dump($class);
				trigger_error("Class does not exist: " . $class);
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
	 * sets the controllerNamespace
	 * @param [string] $ns
	 * @return  Router
	 */
	public function setControllerNamespace( $ns ){
		$this->controllerNamespace = $ns;
		return $this;
	}

	/**
	 * sets conditional routing logic
	 * @param  [string|array]  $conditionals
	 * @param  [controller]  $controller
	 * @return this
	 */
	public function is( $conditionals, $controller ){
		if( is_string( $conditionals) ){
			$key = json_encode( array( $conditionals => true) );
			$this->conditionalRoutes[ $key ] = $controller;
		}elseif( is_array($conditionals) ){

			// we have to translate true to "true"
			foreach( $conditionals as $key => $value ){
				if( !is_int( $key ) && true === $value ){
					$conditionals[$key] = 'true';
				}
			}

			$key = json_encode($conditionals);

			$this->conditionalRoutes[$key] = $controller;
		}

		return $this;
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
			if( 8 <= $this->logLevel && $this->enableLogging ){
				$this->Logger->write('checking conditional routes...');
			}
			if( false === $self->runConditionalRoutes() ){
				echo "no routes found.";
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

	// slim api shortcut
	public function get($path, $controller){
		$this->callSlimApi('get', $path, $controller);
	}

	// slim api shortcut
	public function post($path, $controller){
		$this->callSlimApi('post', $path, $controller);
	}

	// slim api shortcut
	public function put($path, $controller){
		$this->callSlimApi('put', $path, $controller);
	}

	// slim api shortcut
	public function delete($path, $controller){
		$this->callSlimApi('delete', $path, $controller);
	}

	// slim api shortcut
	public function patch($path, $controller){
		$this->callSlimApi('patch', $path, $controller);
	}

	public function group($path, $callback){
		$this->callSlimApi('group', $path, $callback);
	}

}