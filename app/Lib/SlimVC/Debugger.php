<?php

namespace App\Lib\SlimVC;

class Debugger{

	/**
	 * holds our debug stacktrace
	 * @var array
	 */
	protected $stack = array();

	/**
	 * holds our callable callbacks
	 * @var array
	 */
	protected $callbacks = array();

	/**
	 * actions that will be excluded from the trace
	 * @var array
	 */
	protected $excludes = array(
		'gettext',
		'gettext_with_context',
		'sanitize_key',
		'site_url',
		'admin_url'
	);

	protected $includes = array(
		'registered_post_type',
		'registered_taxonomy',
		'wp_headers',
		'send_headers',
		'wp_register_sidebar_widget',
		'after_setup_theme',
		'set_current_user',
		'map_meta_cap',
		'user_has_cap',
		'init',
		'query'
	);

	/**
	 * changes to true when 'shutdown' action is done.
	 * @var boolean
	 */
	public $done = false;

	/**
	 * constructor: adds a action callback for all wp-actions
	 */
	public function __construct(){
		add_action('all', array($this, 'callHandler'), 99999, 99);
	}

	/**
	 * gets the debug stack trace
	 * @return [array]
	 */
	public function getStack(){
		return $this->stack;
	}

	/**
	 * prints the debug stack trace
	 * @return voide
	 */
	public function printStack(){
		$output = '';
		foreach($this->list as $slice){
			$output .= $slice->action . $this->formatArgs($slice->action, $slice->args) . "\n";
		}
		echo '<pre>' . $output . '</pre>';
	}

	public function formatArgs( $action, $arg){
		 switch(true){
		 	case $action === "after_setup_theme":
		 		return;
		 	case 	$action === "registered_post_type"
		 			||
		 			$action === "registered_taxonomy"
		 			||
		 			$action === "query":
		 		return ": " . $arg[1];
		 	case $action === "wp_register_sidebar_widget":
		 		return ": " . $arg[1]['name'];
		 	case $action === "map_meta_cap":
		 		return ": " . $arg[1][0];
		 	case $action === "user_has_cap":
		 		return ": " . $arg[3][0] . ": " . $arg[3][1];
		 	case $action === "set_current_user":
		 		$user = wp_get_current_user();
		 		return ": " . $user->data->user_nicename;
		}
	}

	/**
	 * adds a callable listener to internal callbacks
	 * that will be invoked when shutdown action is done
	 * @param [callable] $callable
	 */
	public function addListener( $callable){
		if( is_callable($callable) ){
			$this->callbacks[] = $callable;
		}
	}

	/**
	 * the callback for ALL wordpress actions
	 * @param  [string] $action
	 * @return [void]
	 */
	public function callHandler( $action ){
		$action = current_filter();
		$args = func_get_args();

		// in includes
		if( in_array($action, $this->includes) ){
			$obj = new \StdClass;
			$obj->action = $action;
			$obj->args = $args;
			$this->list[] = $obj;
		}

		if( 'shutdown' === $action ){
			$this->done = true;
			$this->executeCallbacks();
		}
	}

	/**
	 * executes the registered callbacks
	 * @return [void]
	 */
	protected function executeCallbacks(){
		foreach( $this->callbacks as $callable ){
			call_user_func($callable, $this->stack);
		}
	}
}