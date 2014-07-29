<?php

namespace App\Lib\SlimVC;

//
// seen here:
// http://stackoverflow.com/questions/19328475/adding-custom-page-template-from-plugin#answer-22046128
class Template {

    /**
     * The array of templates that this plugin tracks.
     */
    protected $pageTemplates = array();

    /**
     * Initializes the plugin by setting filters and administration functions.
     */
    public function __construct() {

        $this->pageTemplates = array();

        // Add filter to inject page template
        add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'registerPageTemplate' ) );
        add_filter( 'wp_insert_post_data', array( $this, 'registerPageTemplate' ) );
        add_filter( 'template_include', array( $this, 'viewPageTemplate') );

    }

    /**
     * adds a Page Template
     * @param [string] $name
     * @param [string] $slug
     * @return Template
     */
    public function addPageTemplate( $name, $slug ){
        $this->pageTemplates[$slug] = $name;
        return $this;
    }

    /**
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     */
    public function registerPageTemplate( $atts ) {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        // Retrieve the cache list. 
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
                $templates = array();
        } 

        // New cache, therefore remove the old one
        wp_cache_delete( $cache_key , 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates, $this->pageTemplates );

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;

    } 

    /**
     * Checks if the template is assigned to the page
     */
    public function viewPageTemplate( $template ) {

        global $post;
        $meta = get_post_meta( $post->ID, '_wp_page_template', true );

        if (!isset($this->pageTemplates[$meta] ) ) {
            return $template;
        } 

        $file = plugin_dir_path(__FILE__) . get_post_meta( 
            $post->ID, '_wp_page_template', true 
        );

        // Just to be safe, we check if the file exist first
        if( file_exists( $file ) ) {
                return $file;
        }else{
            echo $file;
        }

        return $template;

    } 


} 