<?php

class WordPress_Travel_Maps_Admin
{
    private $plugin_name;
    private $version;

    /**
     * Construct Store Locator Admin Class
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @param   string                         $plugin_name
     * @param   string                         $version    
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Enqueue Admin Styles
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @return  boolean
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name.'-custom', plugin_dir_url(__FILE__).'css/wordpress-travel-maps-admin.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name.'-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css', array(), $this->version, 'all');
    }

    /**
     * Enqueue Admin Scripts
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @return  boolean
     */
    public function enqueue_scripts()
    {
        global $wordpress_travel_maps_options;

        $mapsJS = 'https://maps.google.com/maps/api/js?sensor=false&libraries=places';
        $googleApiKey = $wordpress_travel_maps_options['apiKey'];
        if (!empty($googleApiKey)) {
            $mapsJS = $mapsJS.'&key='.$googleApiKey;
        }
        $google_maps_url = apply_filters( 'rwmb_google_maps_url', $mapsJS );
        wp_register_script( 'google-maps', esc_url_raw( $google_maps_url ), array(), '', true );

        wp_enqueue_script($this->plugin_name.'-custom', plugin_dir_url(__FILE__).'js/wordpress-travel-maps-admin.js', array('jquery', 'google-maps'), $this->version, true);
    }

    /**
     * Load Extensions
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @return  boolean
     */
    public function load_extensions()
    {
        // Load the theme/plugin options
        if (file_exists(plugin_dir_path(dirname(__FILE__)).'admin/options-init.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'admin/options-init.php';
        }

        // Load Meta Box Map Extend Field
        if (file_exists(plugin_dir_path(dirname(__FILE__)).'admin/travel_map_field.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'admin/travel_map_field.php';
        }
    }

    public function ajax_save_directions() 
    {
        $id = $_POST['id'];
        $directions = $_POST['directions'];

        update_post_meta( $id, 'directions', $directions);

        wp_die('Test');
    }


    public function ajax_get_directions() 
    {
        $id = $_POST['id'];

        $directions = get_post_meta( $id, 'directions', true );
        echo $directions;
        wp_die();
    }
    
    public function vc_menu_icon()
    {
        $travel_maps = get_posts( 'post_type="travel_maps"&numberposts=-1' );

        $travel_maps_select = array();
        if ( $travel_maps ) {
            foreach ( $travel_maps as $travel_map ) {
                $travel_maps_select[ $travel_map->post_title ] = $travel_map->ID;
            }
        } else {
            $travel_maps_select[ __( 'No travel maps found', 'js_composer' ) ] = 0;
        }

        vc_map( array(
            "name" => __( "Travel Map", "wordpress-travel-maps" ),
            "base" => "travel_map",
            "class" => "wordpress-travel-maps",
            "category" => __( "Restaurant", "wordpress-travel-maps"),
            "params" => array(
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Select travel map form', 'js_composer' ),
                    'param_name' => 'id',
                    'value' => $travel_maps_select,
                    'save_always' => true,
                    'description' => __( 'Choose previously created travel map from the drop down list.', 'wordpress-travel-maps' ),
                ),
                array(
                    "type" => "textfield",
                    "holder" => "div",
                    "class" => "",
                    "heading" => __( "Map Width", "wordpress-travel-maps" ),
                    "param_name" => "width",
                    "value" => "100%",
                    "description" => __( "Enter Map width (e.g. 100%)", "wordpress-travel-maps" )
                ),
                array(
                    "type" => "textfield",
                    "holder" => "div",
                    "class" => "",
                    "heading" => __( "Map Height", "wordpress-travel-maps" ),
                    "param_name" => "height",
                    "value" => "350px",
                    "description" => __( "Enter Map height (e.g. 100%)", "wordpress-travel-maps" )
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __( "Show Directions Button", "wordpress-travel-maps" ),
                    "param_name" => "directions_button",
                    "value"      => array(
                        "No"  => 'no',
                        "Yes"   => 'yes'
                    ),
                ),
                array(
                    "type" => "textfield",
                    "holder" => "div",
                    "class" => "",
                    "heading" => __( "Directions Button Text", "wordpress-travel-maps" ),
                    "param_name" => "directions_button_text",
                    "value" => "Get Directions",
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => __( 'Design options', 'wordpress-travel-maps' ),
                    'param_name' => 'css',
                    'group' => __( 'Design options', 'wordpress-travel-maps' ),
                ),
            )
        ) );
    }
}
