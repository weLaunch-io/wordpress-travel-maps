<?php

class WordPress_Travel_Maps_Public
{
    private $plugin_name;
    private $version;
    private $options;

    /**
     * Store Locator Plugin Construct
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
     * Enqueue Styles
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @return  boolean
     */
    public function enqueue_styles()
    {
        global $wordpress_travel_maps_options;

        $this->options = $wordpress_travel_maps_options;

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__).'css/wordpress-travel-maps-public.css', array(), $this->version, 'all');

        $customCSS = $this->get_option('customCSS');

        file_put_contents(dirname(__FILE__)  . '/css/wordpress-travel-maps-custom.css', $customCSS);

        wp_enqueue_style($this->plugin_name.'-custom', plugin_dir_url(__FILE__).'css/wordpress-travel-maps-custom.css', array(), $this->version, 'all');

        return true;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @return  boolean
     */
    public function enqueue_scripts()
    {
        global $wordpress_travel_maps_options;

        $this->options = $wordpress_travel_maps_options;

        $mapsJS = 'https://maps.google.com/maps/api/js?sensor=false&libraries=places';
        $googleApiKey = $this->get_option('apiKey');
        if (!empty($googleApiKey)) {
            $mapsJS = $mapsJS.'&key='.$googleApiKey;
        }

        wp_enqueue_script($this->plugin_name.'-gmaps', $mapsJS, array(), $this->version, true);
        wp_enqueue_script($this->plugin_name.'-public', plugin_dir_url(__FILE__).'js/wordpress-travel-maps-public.js', array('jquery', $this->plugin_name.'-gmaps'), $this->version, true   );

        $forJS = array( 
            'ajax_url' => admin_url('admin-ajax.php'),
        );
        wp_localize_script($this->plugin_name.'-public', 'travel_maps_options', $forJS);

        $customJS = $this->get_option('customJS');
        if (empty($customJS)) {
            return false;
        }

        file_put_contents(dirname(__FILE__)  . '/js/wordpress-travel-maps-custom.js', $customJS);

        wp_enqueue_script($this->plugin_name.'-custom', plugin_dir_url(__FILE__).'js/wordpress-travel-maps-custom.js', array('jquery'), $this->version, false);

        return true;
    }

    /**
     * Get Options
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @param   mixed                         $option The option key
     * @return  mixed                                 The option value
     */
    private function get_option($option)
    {
        if(!is_array($this->options)) {
            return false;
        }

        if (!array_key_exists($option, $this->options)) {
            return false;
        }

        return $this->options[$option];
    }

    /**
     * Init the Store Locator
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @return  boolean
     */
    public function init_WordPress_Travel_Maps()
    {
        global $wordpress_travel_maps_options;

        $this->options = $wordpress_travel_maps_options;

        if (!$this->get_option('enable')) {
            return false;
        }

        add_shortcode('travel_map', array($this, 'get_travel_map'));

        return true;
    }

    /**
     * Create the travel_map locator
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     */
    public function get_travel_map($atts, $content)
    {
        extract(shortcode_atts(array(
            'id' => 'id',
            'width' => '100%',
            'height' => '500px',
            'directions_button' => 'no',
            'directions_button_text' => 'Get Directions',
        ), $atts));

        $html = '<div class="wordpress-travel-map-container">';
            $html .= '<div id="wordpress-travel-map-' . $id . '" data-id="' . $id . '" class="wordpress-travel-map" style="height: ' . $height . '; width: ' . $width . ';"></div>';

            if($directions_button == "yes") {

                $directions = get_post_meta( $id, 'directions', true );
                if(!empty($directions)) {
                    $directions = json_decode($directions);

                    $href = "";

                    if(isset($directions->start->lat)) {
                        $start = $directions->start->lat . ',' . $directions->start->lng;
                        $href = 'https://www.google.de/maps/dir/' . $start;
                    }

                    if(isset($directions->waypoints)) {
                        foreach ($directions->waypoints as $waypoint) {
                            $href .= '/' . $waypoint->location->lat . ',' . $waypoint->location->lng;
                        }
                    }

                    if(isset($directions->end->lat)) {
                        $end = $directions->end->lat . ',' . $directions->end->lng;
                        $href .= '/' . $end;
                    }
                }

                $html .= '<a href="' . $href . '" target="_blank" id="wordpress-travel-map-directions-button-' . $id . '" class="wordpress-travel-map-directions-button button btn btn-default button-primary theme-button">' . $directions_button_text . '</a>';
            }

        $html .= '</div>';

        return $html;


    }
}
