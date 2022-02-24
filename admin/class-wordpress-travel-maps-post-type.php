<?php
/**
 * Custom Post Type for Travel Maps and Taxonomies.
 */
class WordPress_Travel_Maps_Post_Type
{
    private $plugin_name;
    private $version;
    /**
     * Constructor.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     *
     * @param string $plugin_name
     * @param string $version
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_filter('manage_travel_maps_posts_columns', array($this, 'columns_head'));
        add_action('manage_travel_maps_posts_custom_column', array($this, 'columns_content'), 10, 1);
    }

    /**
     * Init.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     *
     * @return bool
     */
    public function init_WordPress_Travel_Maps_Post_Type()
    {
        $this->register_travel_map_locator_post_type();
        $this->register_travel_map_locator_taxonomy();
    }

    /**
     * Register Travel Map Post Type.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     *
     * @return bool
     */
    public function register_travel_map_locator_post_type()
    {
        $singular = __('Travel Map', 'wordpress-travel-maps');
        $plural = __('Travel Maps', 'wordpress-travel-maps');

        $labels = array(
            'name' => __('Travel Maps', 'wordpress-travel-maps'),
            'all_items' => sprintf(__('All %s', 'wordpress-travel-maps'), $plural),
            'singular_name' => $singular,
            'add_new' => sprintf(__('New %s', 'wordpress-travel-maps'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'wordpress-travel-maps'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'wordpress-travel-maps'), $singular),
            'new_item' => sprintf(__('New %s', 'wordpress-travel-maps'), $singular),
            'view_item' => sprintf(__('View %s', 'wordpress-travel-maps'), $plural),
            'search_items' => sprintf(__('Search %s', 'wordpress-travel-maps'), $plural),
            'not_found' => sprintf(__('No %s found', 'wordpress-travel-maps'), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in trash', 'wordpress-travel-maps'), $plural),
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'exclude_from_search' => true,
            'show_ui' => true,
            'menu_position' => 57,
            'rewrite' => false,
            'query_var' => 'travel_maps',
            'supports' => array('title', 'author'),
            'menu_icon' => 'dashicons-location-alt',
        );

        register_post_type('travel_maps', $args);
    }

    /**
     * Register Travel Map Categories and Travel Map Filter Taxonomies.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     *
     * @return bool
     */
    public function register_travel_map_locator_taxonomy()
    {
    	// Travel Map Category
        $singular = __('Travel Map Category', 'wordpress-travel-maps');
        $plural = __('Travel Map Categories', 'wordpress-travel-maps');

        $labels = array(
            'name' => sprintf(__('%s', 'wordpress-travel-maps'), $plural),
            'singular_name' => sprintf(__('%s', 'wordpress-travel-maps'), $singular),
            'search_items' => sprintf(__('Search %s', 'wordpress-travel-maps'), $plural),
            'all_items' => sprintf(__('All %s', 'wordpress-travel-maps'), $plural),
            'parent_item' => sprintf(__('Parent %s', 'wordpress-travel-maps'), $singular),
            'parent_item_colon' => sprintf(__('Parent %s:', 'wordpress-travel-maps'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'wordpress-travel-maps'), $singular),
            'update_item' => sprintf(__('Update %s', 'wordpress-travel-maps'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'wordpress-travel-maps'), $singular),
            'new_item_name' => sprintf(__('New %s Name', 'wordpress-travel-maps'), $singular),
            'menu_name' => sprintf(__('%s', 'wordpress-travel-maps'), $plural),
        );

        $args = array(
                'labels' => $labels,
                'public' => false,
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => array('slug' => 'travel_map-categories'),
        );

        register_taxonomy('travel_map_category', 'travel_maps', $args);
    }

    /**
     * Columns Head.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     *
     * @param string $columns Columnd
     *
     * @return string
     */
    public function columns_head($columns)
    {
        $output = array();
        foreach ($columns as $column => $name) {
            $output[$column] = $name;

            if ($column === 'title') {
                $output['start'] = __('Start', 'wordpress-travel-maps');
                $output['end'] = __('End', 'wordpress-travel-maps');
            }
        }

        return $output;
    }

    /**
     * Columns Content.
     *
     * @author Daniel Barenkamp
     *
     * @version 1.0.0
     *
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     *
     * @param string $column_name Column Name
     *
     * @return string
     */
    public function columns_content($column_name)
    {
        global $post;

        if ($column_name == 'start') {
            echo rwmb_meta('wordpress_travel_maps_start');
        }

        if ($column_name == 'end') {
            echo rwmb_meta('wordpress_travel_maps_end');
        }
    }
}
