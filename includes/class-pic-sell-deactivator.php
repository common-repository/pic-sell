<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://portfolio.cestre.fr
 * @since      1.0.0
 *
 * @package    Pic_Sell
 * @subpackage Pic_Sell/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Pic_Sell
 * @subpackage Pic_Sell/includes
 * @author     Benjamin CESTRE <benjamin@cestre.fr>
 */
class Pic_Sell_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

        if (wp_next_scheduled('mp_cron_import')) {
            $timeStamp = wp_next_scheduled('picsell_cron_task');
            wp_unschedule_event( $timeStamp, 'picsell_cron_task');
        }

	}

}
