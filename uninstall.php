<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   gw2-wvw-matchups
 * @author    Klaufel <klaufel@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.klaufel.info
 * @copyright 2013 Your Name or Company Name
 */

// If uninstall, not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// gw2-wvw-matchups: Define uninstall functionality here