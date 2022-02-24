<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Travel_Map_Field' ) )
{
	class RWMB_Travel_Map_Field extends RWMB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			/**
			 * Allows developers load more libraries via a filter.
			 * @link https://developers.google.com/maps/documentation/javascript/libraries
			 */

		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			$meta = get_post_meta(get_the_ID());

			$html = '<div class="rwmb-map-field">';

			$html .= 
				'<div class="wordpress-travel-map-container">
					<div id="wordpress-travel-map" class="wordpress-travel-map" data-default-loc="%s"></div>
					<div class="wordpress-travel-panel">
						<label for="wordpress_travel_maps_travelmode">Travel Mode </label><select name="wordpress_travel_maps_travelmode" class="wordpress-travel-map-travelmode">';
						if($meta['wordpress_travel_maps_travelmode'][0] == "DRIVING") {
							$html .= '<option selected="selected" value="DRIVING">DRIVING</option>';
						} else {
							$html .= '<option value="DRIVING">DRIVING</option>';
						}
						if($meta['wordpress_travel_maps_travelmode'][0] == "BICYCLING") {
							$html .= '<option selected="selected" value="BICYCLING">BICYCLING</option>';
						} else {
							$html .= '<option value="BICYCLING">BICYCLING</option>';
						}
						if($meta['wordpress_travel_maps_travelmode'][0] == "TRANSIT") {
							$html .= '<option selected="selected" value="TRANSIT">TRANSIT</option>';
						} else {
							$html .= '<option value="TRANSIT">TRANSIT</option>';
						}
						if($meta['wordpress_travel_maps_travelmode'][0] == "WALKING") {
							$html .= '<option selected="selected" value="WALKING">WALKING</option>';
						} else {
							$html .= '<option value="WALKING">WALKING</option>';
						}
			$html .=
						'</select>
						<h3>Start</h3>
						<input class="wordpress-travel-map-start" name="wordpress_travel_maps_start" value="' . $meta['wordpress_travel_maps_start'][0] . '" type="text" placeholder="Start Location">
						<h3>Waypoints</h3>
						<div class="wordpress-travel-map-waypoints">';

						if(!empty($meta['wordpress_travel_maps_waypoints'][0])) {
							$waypoints = json_decode($meta['wordpress_travel_maps_waypoints'][0]);

							if(is_array($waypoints)) {
								foreach ($waypoints as $waypoint) {
									$html .= '	<div class="wordpress-travel-map-waypoint-container">
													<input class="wordpress-travel-map-waypoint" name="wordpress_travel_maps_waypoints[]" value="' . $waypoint . '" type="text" placeholder="Waypoint Location">
													<a class="wordpress-travel-map-waypoint-remove" href="#">X</a>
										 		</div>';
								}
							}
						} else {
							$html .= '	<div class="wordpress-travel-map-waypoint-container">
											<input class="wordpress-travel-map-waypoint" name="wordpress_travel_maps_waypoints[]" type="text" placeholder="Waypoint Location">
											<a class="wordpress-travel-map-waypoint-remove" href="#">X</a>
									 	</div>';
						}
							
						
			$html .= '</div>
						<a id="wordpress-travel-map-add-waypoint" href="#">Add Waypoint</a>
						<br/>
						<h3>Destination</h3>
						<input class="wordpress-travel-map-end" name="wordpress_travel_maps_end" value="' . $meta['wordpress_travel_maps_end'][0] . '" type="text" placeholder="End Location">
						<input class="wordpress-travel-map-calculate-route button button-primary button-large" type="submit" value="Calculate Route">
					</div>
				</div>';

			$html .= '</div>';

			return $html;
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{

			$field = wp_parse_args( $field, array(
				'std'           => '',
				'address_field' => '',
			) );

			return $field;
		}

		/**
		 * Get the field value
		 * The difference between this function and 'meta' function is 'meta' function always returns the escaped value
		 * of the field saved in the database, while this function returns more meaningful value of the field
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Not used for this field
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return mixed Array(latitude, longitude, zoom)
		 */
		// static function get_value( $field, $args = array(), $post_id = null )
		// {
		// 	$value = parent::get_value( $field, $args, $post_id );
		// 	list( $latitude, $longitude, $zoom ) = explode( ',', $value . ',,' );
		// 	return compact( 'latitude', 'longitude', 'zoom' );
		// }

		/**
		 * Save meta value.
		 *
		 * @param mixed $new     The submitted meta value.
		 * @param mixed $old     The existing meta value.
		 * @param int   $post_id The post ID.
		 * @param array $field   The field parameters.
		 */
		public static function save( $new, $old, $post_id, $field ) {

			update_post_meta($post_id, 'wordpress_travel_maps_travelmode', $_POST['wordpress_travel_maps_travelmode']);
			update_post_meta($post_id, 'wordpress_travel_maps_start', $_POST['wordpress_travel_maps_start']);
			update_post_meta($post_id, 'wordpress_travel_maps_waypoints', json_encode( $_POST['wordpress_travel_maps_waypoints']) );
			update_post_meta($post_id, 'wordpress_travel_maps_end', $_POST['wordpress_travel_maps_end']);

			// delete_post_meta( $post_id, $field['id'] );
			// parent::save( $new, array(), $post_id, $field );

		}
	}
}
