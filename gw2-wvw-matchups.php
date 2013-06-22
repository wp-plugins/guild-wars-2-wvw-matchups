<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that also follow
 * WordPress coding standards and PHP best practices.
 *
 * @package   gw2-wvw-matchups
 * @author    Klaufel <klaufel@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.klaufel.info
 * @copyright 2013 Your Name or Company Name
 *
 * @gw2-wvw-matchups
 * Plugin Name: Guild Wars 2 - WvW Matchups
 * Plugin URI:  http://wordpress.org/plugins/gw2-wvw-matchups
 * Description: Plugin to display the live scores of WvW matchups in Guild Wars 2. Working with the API developed by ArenaNet.
 * Version:     1.0.0
 * Author:      Klaufel
 * Author URI:  http://www.klaufel.info
 * Text Domain: gw2-wvw-matchups-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

if ( ! defined( 'WPINC' ) ) { die; }

require_once( plugin_dir_path( __FILE__ ) . 'class-gw2-wvw-matchups.php' );

register_activation_hook( __FILE__, array( 'class_gw2_wvw_matchups', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'class_gw2_wvw_matchups', 'deactivate' ) );

class_gw2_wvw_matchups::get_instance();


class wvw_matchups_widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'wvw_matchups_widget', // Base ID
			'GW2 - WvW Matchups', // Name
			array( 'description' => __( 'Use this widget to display the live scores of WvW matchups in Guild Wars 2', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		require_once 'src/PhpGw2Api/Service.php';
		$service = new PhpGw2Api\Service(__DIR__ . '/cache', 3600);
		$service->returnAssoc(true);
		$home_world = $instance['sample_dropdown'];	
		$a = $service->getMatches();	
		foreach ($a as $v1) {
		    foreach ($v1 as $v2) {
		    	if(($v2['red_world_id'] == $home_world)||($v2['blue_world_id'] == $home_world)||($v2['green_world_id'] == $home_world)) {
		    		$wvw_match_id = $v2['wvw_match_id'];	
					$red_world_id = $v2['red_world_id'];
					$blue_world_id = $v2['blue_world_id'];
					$green_world_id = $v2['green_world_id'];
					if($home_world == $red_world_id) { $home_world_red_class = "home-world"; }
					if($home_world == $blue_world_id) { $home_world_blue_class = "home-world"; }
					if($home_world == $green_world_id) { $home_world_green_class = "home-world"; }
		    	}
				
		    }
		}
		$item = $service->getMatchDetails(array('match_id' => $wvw_match_id));	
		$a = $service->getWorldNames(array('lang' => 'es'));
		foreach ($a as $v1) {
	    	if($v1['id'] == $red_world_id) { $red_world_name = $v1['name']; }
			if($v1['id'] == $blue_world_id) { $blue_world_name = $v1['name']; }
			if($v1['id'] == $green_world_id) { $green_world_name = $v1['name']; }
		}
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		?>		
		<style>
			#gw2-wvw-matchups .match { padding: 5px 10px; margin: 10px 0px;}
			#gw2-wvw-matchups .match.red { background: #AA4644; color: #fff; }
			#gw2-wvw-matchups .match.green { background: #89A54E; color: #fff; }
			#gw2-wvw-matchups .match.blue { background: #4573A7; color: #fff; }
			#gw2-wvw-matchups .match .home-world { padding-left: 18px; background: url('<?php bloginfo('url'); ?>/wp-content/plugins/gw2-wvw-matchups/assets/home-world.png') left center no-repeat; }
			#gw2-wvw-matchups .match .world { font-size: 16px; display: block;  }
			#gw2-wvw-matchups .match .points { font-size: 28px; display: block; margin-top: 5px; }
		</style>
		<div id="gw2-wvw-matchups">
			<div class="match red"><span class="world <?php echo $home_world_red_class; ?>"><?php echo $red_world_name; ?><?php //echo " |  ".$red_world_id; ?></span><span class="points"><?php echo number_format($item['scores'][0]);?></span></div>
			<div class="match blue"><span class="world <?php echo $home_world_blue_class; ?>"><?php echo $blue_world_name; ?><?php //echo " |  ".$blue_world_id; ?></span><span class="points"><?php echo number_format($item['scores'][1]);?></span></div>
			<div class="match green"><span class="world <?php echo $home_world_green_class; ?>"><?php echo $green_world_name; ?><?php //echo " |  ".$green_world_id; ?></span><span class="points"><?php echo number_format($item['scores'][2]);?></span></div>
		</div>
		<?php
		
		echo $after_widget;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		require_once 'src/PhpGw2Api/Service.php';
		$service = new PhpGw2Api\Service(__DIR__ . '/cache', 3600);
		$service->returnAssoc(true);		
		if ( isset( $instance[ 'title' ] ) ) { $title = $instance[ 'title' ]; }
		else { $title = __( 'WvW Score', 'text_domain' ); }
		$sample_dropdown = esc_attr( $instance[ 'sample_dropdown' ] );
		?>
		<p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('sample_dropdown'); ?>"><?php _e( 'World:' ); ?></label>
			<select name="<?php echo $this->get_field_name('sample_dropdown'); ?>" id="<?php echo $this->get_field_id('sample_dropdown'); ?>" class="widefat">
			<?php
			$prueba = $service->getWorldNames();	
			foreach($prueba as $valor ) {			
				?><option value="<?php echo $valor['id']; ?>"<?php selected( $instance['sample_dropdown'], $valor['id'] ); ?>><?php _e( $valor['name'], 'dxbase' ); ?></option><?php	
			} 	
			?>
			</select>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['sample_dropdown'] = strip_tags($new_instance['sample_dropdown']);
		return $instance;
	}

} // class wvw_matchups_widget


// register wvw_matchups_widget
add_action( 'widgets_init', function() { register_widget( 'wvw_matchups_widget' ); } );