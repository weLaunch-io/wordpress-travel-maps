<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              http://woocommerce.db-dzine.de
 * @since             1.0.0
 * @package           WordPress_Travel_Maps
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Travel Maps
 * Plugin URI:        http://woocommerce.db-dzine.de
 * Description:       Add a Store Locator to your WooCommerce Shop!
 * Version:           1.0.4
 * Author:            DB-Dzine
 * Author URI:        http://www.db-dzine.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordpress-travel-maps
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wordpress-travel-maps-activator.php
 */
function activate_WordPress_Travel_Maps() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-travel-maps-activator.php';
	WordPress_Travel_Maps_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wordpress-travel-maps-deactivator.php
 */
function deactivate_WordPress_Travel_Maps() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-travel-maps-deactivator.php';
	WordPress_Travel_Maps_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_WordPress_Travel_Maps' );
register_deactivation_hook( __FILE__, 'deactivate_WordPress_Travel_Maps' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-travel-maps.php';

/**
 * Run the Plugin
 * @author Daniel Barenkamp
 * @version 1.0.0
 * @since   1.0.0
 * @link    http://woocommerce.db-dzine.de
 */
function run_WordPress_Travel_Maps() {

	$plugin_data = get_plugin_data( __FILE__ );
	$version = $plugin_data['Version'];

	$plugin = new WordPress_Travel_Maps($version);
	$plugin->run();

}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
// Load the TGM init if it exists
if ( file_exists( plugin_dir_path( __FILE__ ) . 'admin/tgm/tgm-init.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'admin/tgm/tgm-init.php';
}

if ( is_plugin_active('redux-framework/redux-framework.php') && is_plugin_active('meta-box/meta-box.php')){
	run_WordPress_Travel_Maps();
} else {
	add_action( 'admin_notices', 'run_WordPress_Travel_Maps_Not_Installed' );
}

function run_WordPress_Travel_Maps_Not_Installed()
{
	?>
    <div class="error">
      <p><?php _e( 'WordPress Travel Maps requires the Redux Framework & Meta Boxes plugin. Please install or activate it before!', 'wordpress-travel-maps'); ?></p>
    </div>
    <?php
}
