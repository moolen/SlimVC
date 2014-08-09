<?php

namespace App\Lib\SlimVC;

/**
 * PostModel
 * Basic abstraction Layer for the wordpress Post "class"
 *
 * @author Michael Bindig <michael.bindig@sopg.de>
 */
class PostModel {
    
    /**
     * WordPress post object.
     * 
     * @var WP_Post
     */
    protected $post = null;
   
    /**
     * Featured image's URL.
     * 
     * @var string
     */
    protected $featuredImageUrl = null;
    
    /**
     * Custom fields array.
     * 
     * @var array
     */
    protected $cFields = array();
       
    
    /**
     * Initializes this SOPG post object, by loading all 
     * 
     * @param WP_Post $post
     */
    protected function initialize(\WP_Post $post)
    {
        $this->post = $post;
        
        $this->loadCustomFieldsArray();
        $this->loadFeaturedImage();
    }
    
    
    /**
     * Loads all custom fields into $cFields array.
     * 
     * @author Michael Bindig <michael.bindig@sopg.de>
     * 
     * @return void
     */
    protected function loadCustomFieldsArray()
    {
        if (is_null($this->post))
            return;
        
        $cfAssocArray = get_post_meta($this->post->ID);
        
        foreach ($cfAssocArray as $key => $val) 
        {
            $this->cFields[$key] = (1 < count($val)) ? $val : $val[0];
        }
    }
    
    
    /**
     * Loads the featured image URL into this object.
     *
     * @author Michael Bindig <michael.bindig@sopg.de>
     *
     * @return void
     */
    protected function loadFeaturedImage()
    {
        $postThumbnail = wp_get_attachment_url(get_post_thumbnail_id($this->post->ID));

        if ($postThumbnail)
        {
            $this->featuredImageUrl = $postThumbnail;
        }
    }
    

    /**
     * Construct!
     * 
     * @author Michael Bindig <michael.bindig@sopg.de>
     * 
     * @param WP_Post|int $post
     * @throws \Exception
     */
    public function __construct($post) 
    {
        if ($post instanceof \WP_Post)
        {
            $this->initialize($post);
        }
        elseif ($post instanceof PostModel)
        {
            $this->initialize($post->post);
        }
        elseif (is_numeric($post) && 0 < (int) $post)
        {
            $wpPost = get_post((int) $post);
            
            if (is_null($wpPost))
            {
                throw new \Exception('An error occured, while trying to fetch WP_Post with ID#' . (int) $post);
            }
            
            $this->initialize($wpPost);
        }
        else
        {
            throw new \Exception('Given $post parameter is neither instance of WP_Post class, nor instance of PostModel class and it is no valid integer either.');
        }
    }
    
    
    /**
     * Returns the WordPress post's title. If $strlen is given,
     * the title will be cut to the given length, depending
     * on the last whitespace. If $raw is TRUE, no WordPress filter
     * will be applied on the title.
     *
     * @author Michael Bindig <michael.bindig@sopg.de>
     *
     * @param int $strlen (optional) The character-length of the returned string. Leave blank or set to NULL to get the whole title
     * @param boolean $raw (optional) Set to TRUE, if no WordPress filters shall be applied (Default: FALSE)
     * @param string $closure (optional) String, used at the end of shortened post titles (Default: '...')
     * 
     * @return string Post title.
     */
    public function getTitle($strlen = null, $raw = false, $closure = '...')
    {
        if (!$raw)
        {
            /** Apply the WordPress filters * */
            $title = do_shortcode(apply_filters('the_title', $this->post->post_title));
        }
        else
        {
            $title = $this->post->post_title;
        }

        if ($strlen !== null && is_numeric($strlen) && 0 < (int) $strlen)
        {
            if (strlen($title) > (int) $strlen)
            {
                $shortened      = substr(strip_tags($title), 0, (int) $strlen);
                $lastWhiteSpace = strrpos($short, ' ');
                $title          = substr($shortened, 0, $lastWhiteSpace) . $ending;
            }
        }
        
        return $title;
    }
    
    
    /**
     * Returns the underlying WordPress post object.
     * 
     * @author Michael Bindig <michael.bindig@sopg.de>
     * 
     * @return WP_Post WordPress post object
     */
    public function getPost()
    {
        return $this->post;
    }
    
    
    /**
     * Returns the WordPress post's content. If $strlen is given,
     * the content will be cut to the given length, depending
     * on the last whitespace. If $raw is TRUE, no WordPress filters
     * will be applied on the content.
     *
     * @author Michael Bindig <michael.bindig@sopg.de>
     *
     * @param int $strlen (optional) The character-length of the returned string. Leave blank or set to NULL to get the whole content (Default: NULL)
     * @param boolean $raw (optional) Set to TRUE, if no WordPress filters shall be applied (Default: FALSE)
     * 
     * @return string The WordPress post's content.
     */
    public function getContent($strlen = null, $raw = false, $closure = '...')
    {
        if (!$raw)
        {
            /* Apply the WordPress post_content filters. */
            $content = apply_filters('the_content', $this->post->post_content);
        }
        else
        {
            $content = $this->post->post_content;
        }

        if ($strlen !== null && is_numeric($strlen) && 0 < (int) $strlen)
        {
            if (strlen($content) > (int) $strlen)
            {
                $shortened      = substr(strip_tags($content), 0, (int) $strlen);
                $lastWhitespace = strrpos($shortened, ' ');
                $content        = substr($shortened, 0, $lastWhitespace) . $closure;
            }
        }
        return $content;
    }
    
    
    /**
     * Returns the URL of the featured image.
     * 
     * @author Michael Bindig <michael.bindig@sopg.de>
     * 
     * @return string
     */
    public function getFeaturedImageUrl()
    {
        return $this->featuredImageUrl;
    }
    
    
    /**
     * Returns the value of the custom field with the given name.
     * If expected custom field has more than one value,
     * an numeric array will be returned. If no such custom field
     * exists, NULL will be returned.
     * 
     * @author Michael Bindig <michael.bindig@sopg.de>
     * 
     * @param string $name Custom field's name
     * 
     * @return array|string|null
     */
    public function getCustomField($name)
    {
        if (!array_key_exists($name, $this->cFields))
        {
            return null;
        }
            
        return $this->cFields[$name];
    }
    
    
    /**
     * Returns the WordPress post's date. If $format is given, post_date will be
     * returned formatted. Use the format syntax of PHP's date() function.
     *
     * @author Michael Bindig <michael.bindig@sopg.de>
     *
     * @param string $format (optional) Format string, used for PHP's date() function (Default: Empty string)
     * 
     * @return string Formatted or unformatted post_date
     */
    public function getDate($format = '')
    {
        $date = $this->post->post_date;

        if (!empty($format))
        {
            $date = date($format, strtotime($date));
        }

        return $date;
    }    
       
    
    /**
     * Returns an array with all sub-posts, if there are any. 
     * Returns an empty array if no sub-posts can be found.
     *
     * @author Michael Bindig <michael.bindig@sopg.de>
     *
     * @param array $args (optional) Array with arguments, used by the
     *                    WordPress get_children() function (Default: NULL)
     *
     * @return array
     */
    public function getSubPosts(array $args = null)
    {
        $array = array();

        $defaults = array(
            'post_status' => 'publish'
        );

        if (is_array($args))
        {
            $defaults = array_merge($defaults, $args);
        }

        /* Overwrite post_parent key 
        to ensure getting this post's sub-posts. */
        $defaults['post_parent'] = $this->post->ID;
        
        $posts = get_children($defaults);

        /* Finally delete all post ID keys,
        to get a cleaned numeric array. */
        if (0 < count($posts))
        {
            foreach ($posts as $post)
            {
                $array[] = $post;
            }
        }

        return $array;
    }    
    
    
    /**
     * Returns TRUE, if underlying WordPress post
     * has sub-posts, otherwise FALSE.
     * 
     * @author Michael Bindig <michael.bindig@sopg.de>
     * 
     * @return boolean
     */
    public function hasSubPosts()
    {
        $hasSubs = false;
        
        $subs    = $this->getSubPosts();
        
        /* If something is listed ... */
        if (!empty($subs))
        {
            $hasSubs = true;
        }
        
        $subs = null;
        
        return $hasSubs;
    }
    
    
    /**
     * Returns TRUE, if underlying WordPress post 
     * is child of given parent WordPress post.
     *
     * @author Michael Bindig <michael.bindig@sopg.de>
     *
     * @param WP_Post|int $parent WordPress post object or ID
     *
     * @return boolean
     */
    public function isChildOf($parent)
    {
        $return   = false;
        $postModel = new PostModel($parent);
        
        if ((int) $this->post->post_parent === (int) $postModel->post->ID)
        {
            $return = true;
        }
        
        unset($postModel);
        
        return $return;
    }
    
    
    /**
     * Returns TRUE, if WordPress post is connected 
     * to the WordPress category with the given slug,
     * otherwise FALSE will be returned.
     *
     * @author Michael Bindig <michael.bindig@sopg.de>
     *
     * @param string $slug WordPress category slug
     *
     * @return boolean
     */
    public function isInCategory($slug)
    {
        $return   = false;
        $catArray = get_the_category($this->post->ID);

        foreach ($catArray as $catObj)
        {
            if ($slug == $catObj->slug)
            {
                $return = true;
                break;
            }
        }

        return $return;
    }
    
}
