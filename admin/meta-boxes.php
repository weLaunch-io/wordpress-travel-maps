<?php
/**
 * Registering meta boxes
 *
 * All the definitions of meta boxes are listed below with comments.
 * Please read them CAREFULLY.
 *
 * You also should read the changelog to know what has been changed before updating.
 *
 * For more information, please visit:
 * @link http://metabox.io/docs/registering-meta-boxes/
 */

add_filter( 'rwmb_meta_boxes', 'wordpress_travel_maps_register_meta_boxes' );

/**
 * Register meta boxes
 *
 * Remember to change "wordpress_travel_maps" to actual prefix in your project
 *
 * @param array $meta_boxes List of meta boxes
 *
 * @return array
 */

function wordpress_travel_maps_register_meta_boxes( $meta_boxes )
{
	global $wordpress_travel_maps_options;

	$prefix = 'wordpress_travel_maps_';

	$meta_boxes[] = array(
		'title'  => __( 'Address', 'wordpress-travel-maps' ),
		'post_types' => 'travel_maps',
		'fields' => array(
			array(
				'id'            => "{$prefix}map",
				// 'name'          => __( 'Map', 'wordpress-travel-maps' ),
				'type'          => 'travel_map',
				// Default location: 'latitude,longitude[,zoom]' (zoom is optional)
				'std'           => '-6.233406,-35.049906,15',
				// 'address_field' => "{$prefix}address1,{$prefix}address2,{$prefix}zip,{$prefix}city,{$prefix}region,{$prefix}country",
			),
		),
	);

	return $meta_boxes;
}