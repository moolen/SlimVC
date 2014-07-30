<?php

namespace App\Lib\SlimVC;

use \App\Lib\SlimVC\PageTemplate as PageTemplate;


class ConfigManager{

	protected $configDir = '../../Config';
	protected $extension = '.php';
	protected $PageTemplate;
	protected $fileNames = array(
		'application',
		'images',
		'menus',
		'sidebars',
		'templates',
		'postTypes',
		'taxonomies'
	);

	protected $defaultImageSize = array(0,0,false);

	/**
	 * construcor. takes a SLimVC object instnace
	 * @param [SlimVC] $parent
	 */
	public function __construct( $parent ){
		$this->parent = $parent;
		$this->PageTemplate = new PageTemplate();
		$this->readConfigDir();
		$this->applyConfiguration();
		$this->initWpHooks();
	}

	/**
	 * reads the configuration directory
	 * and sets the internals per configuration
	 * @return [void]
	 */
	protected function readConfigDir(){
		foreach( $this->fileNames as $file ){
			$path = trailingslashit(dirname(__FILE__)) . trailingslashit($this->configDir) . $file . $this->extension;
			if( file_exists( $path ) ){
				$this->$file = include $path;
			}else{
				$this->$file = array();
			}
		}
	}

	/**
	 * applys the configuration to wp core
	 * @return [void]
	 */
	protected function applyConfiguration(){

		// register image sizes
		foreach( $this->images as $name => $opts){
			$options = $opts + $this->defaultImageSize;
			add_image_size($name, $options[0], $options[1], $options[2]);
		}

		// add page templates
		foreach( $this->templates as $slug=>$name ){
			$this->PageTemplate->addPageTemplate($name, $slug);
		}

		// add nav menus
		foreach( $this->menus as $slug=>$name ){
			register_nav_menu( $slug, $name );
		}

		// add sidebars
		foreach( $this->sidebars as $config ){
			register_sidebar($config);
		}

		if( is_array($this->application) && is_array($this->application['slim']) ){
			$slimOptions = array_merge( $this->parent->slimOptions, $this->application['slim'] );
			$this->parent->slimOptions = $slimOptions;
			$this->application['slim'] = $slimOptions;
		}
		
		$this->parent->applicationConfiguration = $this->application;

	}

	/**
	 * sets up the CT & CPT registration hooks
	 * @return [void]
	 */
	protected function initWpHooks(){
		$ct = $this->taxonomies;
		$cpt = $this->postTypes;

		$this->parent->on('init', function() use ($ct, $cpt) {
			
			// register CT
			foreach( $ct as $slug=>$opts ){
				
				$args = null;
				$postType = 'post';

				if( is_array($opts) ){
					
					// check for args array
					if( isset($opts['args']) && is_array($opts['args']) && !empty($opts['args']) ){
						$args = $opts['args'];
					}

					// check for postType def
					if( isset($opts['postType']) && !empty($opts['postType']) ){
						$postType = $opts['postType'];
					}
					// register
					register_taxonomy($slug, $postType, $args);
				}
				
			}

			// register CPT
			foreach($cpt as $slug=>$opts){
				register_post_type($slug, $opts);
			}
		});
	}

}