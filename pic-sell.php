<?php

/**
 * 
 * Plugin Name:       Pic Sell
 * Plugin URI:        https://github.com/kimil14/pic-sell
 * Description:       Selling pictures easily.
 * Version:           1.0.11
 * Author:            Benjamin CESTRE
 * Author URI:        https://portfolio.cestre.fr
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pic_sell_plugin
 * Domain Path:       /languages
 * 
 * @package           Pic_Sell
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} 

define( 'PIC_SELL_VERSION', '1.0.11' );
define( 'PIC_SELL_MAIN_FILE',  __FILE__ );
define( 'PIC_SELL_PATH', plugin_dir_path( __FILE__ ));
define( 'PIC_SELL_URL', plugin_dir_url( __FILE__ ));

define( 'PIC_SELL_TEMPLATE_DIR', PIC_SELL_PATH . "templates/");
define( 'PIC_SELL_URL_INC', PIC_SELL_URL . "includes/");
define( 'PIC_SELL_PATH_INC', PIC_SELL_PATH . "includes/");
define( 'PIC_SELL_SLUG', "picsell");

define( 'PIC_SELL_URL_PUBLIC', PIC_SELL_URL . "public/");
define( 'PIC_SELL_PATH_PUBLIC', PIC_SELL_PATH . "public/");

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pic-sell-activator.php
 */
function activate_pic_sell() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pic-sell-activator.php';
	Pic_Sell_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pic-sell-deactivator.php
 */
function deactivate_pic_sell() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pic-sell-deactivator.php';
	Pic_Sell_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pic_sell' );
register_deactivation_hook( __FILE__, 'deactivate_pic_sell' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pic-sell.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pic_sell() {

	$plugin = new Pic_Sell();
	$plugin->run();

}
run_pic_sell();
