<?php
/**
 * Guild Wars 2 - WvW Matchups
 *
 * @package   guild-wars-2-wvw-matchups
 * @author    Klaufel <klaufel@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.klaufel.com/guild-wars-2-wvw-matchups
 */

class class_gw2_wvw_matchups {
	protected $version = '1.0.2';
	protected $plugin_slug = 'gw2_wvw_matchups';
	protected static $instance = null;
	protected $plugin_screen_hook_suffix = null;

	private function __construct() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'gw2_wvw_matchups', array( $this, 'action_method_name' ) );
		add_filter( 'gw2_wvw_matchups', array( $this, 'filter_method_name' ) );
	}
	
	public static function get_instance() {
		if ( null == self::$instance ) { self::$instance = new self; }
		return self::$instance;
	}

	public static function activate( $network_wide ) { }
	public static function deactivate( $network_wide ) { }

	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
}