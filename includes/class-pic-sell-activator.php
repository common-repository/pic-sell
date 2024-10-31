<?php

/**
 * Fired during plugin activation
 *
 * @link       https://portfolio.cestre.fr
 * @since      1.0.0
 *
 * @package    Pic_Sell
 * @subpackage Pic_Sell/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Pic_Sell
 * @subpackage Pic_Sell/includes
 * @author     Benjamin CESTRE <benjamin@cestre.fr>
 */
class Pic_Sell_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        // register cron task
        if (!wp_next_scheduled('picsell_cron_task')) {
            wp_schedule_event( time(), 'daily', 'picsell_cron_task' );
        }

		//flush rewrite
		flush_rewrite_rules();
	}

}
