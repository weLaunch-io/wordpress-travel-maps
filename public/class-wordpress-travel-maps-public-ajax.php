<?php

class WordPress_Travel_Maps_Public_Ajax
{

    private $plugin_name;
    private $version;
    private $options;

    /**
     * Store Locator Ajax Class 
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
     * Get Stores and echo json encoded Data
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @return  JSON    The Stores
     */
    public function get_travel_maps()
    {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            die('No AJAX call!');
        }

        if (!isset($_POST['lat']) || !isset($_POST['lng']) || !isset($_POST['radius'])) {
            header('HTTP/1.1 400 Bad Request', true, 400);
            die('No Lat, Lng or Radius');
        }

        $lat = floatval($_POST['lat']);
        $lng = floatval($_POST['lng']);
        $radius = absint($_POST['radius']);
        if (!is_float($lat) || !is_float($lng) || !absint($radius)) {
            header('HTTP/1.1 400 Bad Request', true, 400);
            die('Not a correct value for Lat, Lng or Radius!');
        }

        $travel_maps = $this->query_travel_maps($lat, $lng, $radius);
        echo json_encode($travel_maps, JSON_FORCE_OBJECT);
        die();
    }

    /**
     * The Database query for getting the right Stores
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @return  array Stores
     */
    public function query_travel_maps($lat, $lng, $radius)
    {
        global $wpdb, $wordpress_travel_maps_options;

        $travel_map_data = array();

        $distanceUnit = ($wordpress_travel_maps_options['mapDistanceUnit'] == 'km') ? 6371 : 3959;

        if (!$radius || empty($radius) || $radius > 999) {
            $radius = $wordpress_travel_maps_options['mapRadius'];
        }

        $resultListMax = $wordpress_travel_maps_options['resultListMax'];

        // Filtering
        if (isset($_POST['categories']) || isset($_POST['filter'])) {
            $filter = '';

            if (!empty($_POST['categories'][0])) {
                $categories_ids = array_map('absint', $_POST['categories']);
                $filter = $filter."
                        INNER JOIN $wpdb->term_relationships AS term_rel ON posts.ID = term_rel.object_id
                        INNER JOIN $wpdb->term_taxonomy AS term_tax ON term_rel.term_taxonomy_id = term_tax.term_taxonomy_id 
                        AND term_tax.taxonomy = 'travel_map_category'
                        AND term_tax.term_id IN (".implode(',', $categories_ids).')';
            }

            if (!empty($_POST['filter'])) {
                $filter_ids = array_map('absint', $_POST['filter']);
                $c = 1;
                foreach ($filter_ids as $filter_id) {
                    $filter = $filter." 
                           INNER JOIN $wpdb->term_relationships AS term_rel".$c.' ON posts.ID = term_rel'.$c.".object_id
                           INNER JOIN $wpdb->term_taxonomy AS term_tax".$c.' ON term_rel'.$c.'.term_taxonomy_id = term_tax'.$c.'.term_taxonomy_id 
                           AND term_tax'.$c.".taxonomy = 'travel_map_filter'
                            AND term_tax".$c.'.term_id = '.$filter_id;
                    ++$c;
                }
                // OR-Operator!
                // $filter = $filter . " 
                //        INNER JOIN $wpdb->term_relationships AS term_rel2 ON posts.ID = term_rel2.object_id
                //        INNER JOIN $wpdb->term_taxonomy AS term_tax2 ON term_rel2.term_taxonomy_id = term_tax2.term_taxonomy_id 
                //        AND term_tax2.taxonomy = 'travel_map_filter'
                //        AND term_tax2.term_id = " . implode( ', ', $filter_ids );
            }
        } else {
            $filter = '';
        }

        $sql = "SELECT 
        			posts.ID,
        			posts.post_title as na,
        			posts.post_content as de,
					post_lat.meta_value AS lat,
                   	post_lng.meta_value AS lng,
                   	( %d * acos( cos( radians( %s ) ) * cos( radians( post_lat.meta_value ) ) * cos( radians( post_lng.meta_value ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( post_lat.meta_value ) ) ) )
                	AS distance
              		FROM $wpdb->posts AS posts
		            INNER JOIN $wpdb->postmeta AS post_lat ON post_lat.post_id = posts.ID AND post_lat.meta_key = 'wordpress_travel_maps_lat'
		            INNER JOIN $wpdb->postmeta AS post_lng ON post_lng.post_id = posts.ID AND post_lng.meta_key = 'wordpress_travel_maps_lng'
                    $filter
             		WHERE posts.post_type = 'travel_maps'
               		AND posts.post_status = 'publish'
               		GROUP BY lat 
                   	HAVING distance < %d ORDER BY distance LIMIT 0, %d";

        $values = array(
            $distanceUnit,
            $lat,
            $lng,
            $lat,
            $radius,
            $resultListMax,
        );

        $travel_maps = $wpdb->get_results($wpdb->prepare($sql, $values));

        if ($travel_maps) {
            $travel_map_data = apply_filters('wordpress_travel_maps_travel_maps', $this->get_meta_data($travel_maps));
        }

        return $travel_map_data;
    }

    /**
     * Get the Stores Metadata
     * Also remove not needed Data and minify the data for the AJAX transfer
     * 
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @param   array $travel_maps Stores
     * @return  array Stores with Meta Data
     */
    public function get_meta_data($travel_maps)
    {
        global $wpdb, $wordpress_travel_maps_options;

        $prefix = 'wordpress_travel_maps_';

        foreach ($travel_maps as $travel_map_key => $travel_map) {

            // Get the post meta data
            $travel_map_metas = get_post_meta($travel_map->ID);

            // Meta Data
            if ($wordpress_travel_maps_options['showStreet']) {
                $travel_map->st = '';
                if(isset($travel_map_metas["{$prefix}address1"][0])) {
                    $travel_map->st .= $travel_map_metas["{$prefix}address1"][0];
                }
                if(isset($travel_map_metas["{$prefix}address2"][0])) {
                    $travel_map->st .= ' '. $travel_map_metas["{$prefix}address2"][0];
                }
            }
            if ($wordpress_travel_maps_options['showCity']) {
                $travel_map->ct = '';
                if(isset($travel_map_metas["{$prefix}zip"][0])) {
                    $travel_map->ct .= $travel_map_metas["{$prefix}zip"][0];
                }
                if(isset($travel_map_metas["{$prefix}city"][0])) {
                    $travel_map->ct .= ' '. $travel_map_metas["{$prefix}city"][0];
                }
            }
            if ($wordpress_travel_maps_options['showCountry']) {
                $travel_map->co = '';
                if(isset($travel_map_metas["{$prefix}region"][0])) {
                    $travel_map->co .= $travel_map_metas["{$prefix}region"][0];
                }
                if(isset($travel_map_metas["{$prefix}country"][0])) {
                    $travel_map->co .= ' '. $travel_map_metas["{$prefix}country"][0];
                }
            }
            if ($wordpress_travel_maps_options['showTelephone'] && isset($travel_map_metas["{$prefix}telephone"][0])) {
                $travel_map->te = $travel_map_metas["{$prefix}telephone"][0];
            }
            if ($wordpress_travel_maps_options['showMobile'] && isset($travel_map_metas["{$prefix}mobile"][0])) {
                $travel_map->mo = $travel_map_metas["{$prefix}mobile"][0];
            }
            if ($wordpress_travel_maps_options['showFax'] && isset($travel_map_metas["{$prefix}fax"][0])) {
                $travel_map->fa = $travel_map_metas["{$prefix}fax"][0];
            }
            if ($wordpress_travel_maps_options['showEmail'] && isset($travel_map_metas["{$prefix}email"][0])) {
                $travel_map->em = $travel_map_metas["{$prefix}email"][0];
            }
            if ($wordpress_travel_maps_options['showWebsite'] && isset($travel_map_metas["{$prefix}website"][0])) {
                $travel_map->we = $travel_map_metas["{$prefix}website"][0];
            }
            if ($wordpress_travel_maps_options['resultListPremiumIconEnabled'] && isset($travel_map_metas["{$prefix}premium"][0])) {
                $travel_map->pr = $travel_map_metas["{$prefix}premium"][0];
            }
            if ($wordpress_travel_maps_options['showStoreFilter']) {
                $args = array('fields' => 'names');
                $travel_map->fi = wp_get_object_terms($travel_map->ID, 'travel_map_filter', $args);
            }
            if ($wordpress_travel_maps_options['showStoreCategories']) {
                $args = array('fields' => 'names');
                $travel_map->ca = wp_get_object_terms($travel_map->ID, 'travel_map_category', $args);
            }
            if ($wordpress_travel_maps_options['showOpeningHours']) {
                $weekdays = array(
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday',
                );
                $c = 0;
                foreach ($weekdays as $weekday) {
                    $travel_map->op[$c++] = $travel_map_metas["{$prefix}{$weekday}_open"][0];
                    $travel_map->op[$c++] = $travel_map_metas["{$prefix}{$weekday}_close"][0];
                }
            }
            // Unset not shown posts fields
            if (!$wordpress_travel_maps_options['showName']) {
                unset($travel_map->na);
            }
            if (!$wordpress_travel_maps_options['showDescription']) {
                unset($travel_map->de);
            }

            $travel_map->ic = $wordpress_travel_maps_options['mapDefaultIcon'];
            if ($wordpress_travel_maps_options['showImage']) {
                $imageURL = $this->get_thumb($travel_map_metas['_thumbnail_id'][0]);

                if(empty($imageURL)) {
                    $imageURL = plugin_dir_url(__FILE__).'img/blank.gif';
                }
                $travel_map->im = $imageURL;
            }
        }

        return $travel_maps;
    }

    /**
     * Get Image Thumb
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://woocommerce.db-dzine.de
     * @param   int                         $image_id Image ID
     * @return  string URL of image
     */
    public function get_thumb($image_id)
    {
        global $wordpress_travel_maps_options;

        $width = substr($wordpress_travel_maps_options['imageDimensions']['width'], 0, -2);
        $height = substr($wordpress_travel_maps_options['imageDimensions']['height'], 0, -2);

        $image = wp_get_attachment_image_src($image_id, array($width, $height));

        return $image[0];
    }
}