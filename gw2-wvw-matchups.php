<?php
/*
Plugin Name: Guild Wars 2 - WvW Matchups
Plugin URI: http://wordpress.org/plugins/guild-wars-2-wvw-matchups
Description: Plugin to display the live scores of WvW matchups in Guild Wars 2. Working with the API developed by ArenaNet.
Version: 1.2.1
Author: klaufel
Author URI: http://www.klaufel.com
License: GPLv2 or later
*/

if ( ! defined( 'WPINC' ) ) { die; }

wp_register_style('guild-wars-2-wvw-matchups', plugins_url('gw2-wvw-matchups.css',__FILE__ ));
wp_enqueue_style('guild-wars-2-wvw-matchups');

require (dirname(__FILE__).'/vesu/SDK/Gw2/Gw2SDK.php');
require (dirname(__FILE__) .'/vesu/SDK/Gw2/Gw2Exception.php');

use vesu\SDK\Gw2\Gw2SDK;
use vesu\SDK\Gw2\Gw2Exception;

class wvw_matchups_widget extends WP_Widget {


	public function __construct() {
		parent::__construct(
	 		'wvw_matchups_widget', // Base ID
			'GW2 - WvW Matchups', // Name
			array( 'description' => __( 'Use this widget to display the live scores of WvW matchups in Guild Wars 2', 'text_domain' ), ) // Args
		);
		
	}

	// Front-end display of widget.
	public function widget( $args, $instance ) {
		extract( $args );
		
		$cachedir = dirname(__FILE__).'/cache';
		if (substr(decoct(fileperms($cachedir)),2) != '777') {
			$gw2 = new Gw2SDK; 
		} else {
			$gw2 = new Gw2SDK(dirname(__FILE__).'/cache');
		}		
				
		$world_id = $instance['sample_dropdown'];	
		$lang = $instance['lang'];	
				
		$matches = $gw2->getMatchByWorldId($world_id);
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if (!empty($title)) { echo $before_title . $title . $after_title; }
		
		foreach($matches as $match) {
			$scores = $gw2->getScoresByMatchId($match->wvw_match_id);
			$url = file_get_contents(dirname(__FILE__).'/world_names.json');
			$arr = json_decode($url,true);
			foreach($arr as $item) {
				if($item['id'] == $match->red_world_id) { $red_world_name = $item['name']; }
				if($item['id'] == $match->blue_world_id) { $blue_world_name = $item['name']; }
				if($item['id'] == $match->green_world_id) { $green_world_name = $item['name']; }				  
			} 
			?>
			<div id="gw2-wvw-matchups">				
				<ul class="gw2-matchups">
					<li class="match red">
						<span class="world home"><?php echo $red_world_name; ?></span>
						<span class="points"><?php echo number_format($scores[0]); ?></span>
					</li>			
					<li class="match blue">
						<span class="world home"><?php echo $blue_world_name; ?></span>
						<span class="points"><?php echo number_format($scores[1]); ?></span>
					</li>			
					<li class="match green">
						<span class="world home"><?php echo $green_world_name; ?></span>
						<span class="points"><?php echo number_format($scores[2]); ?></span>
					</li>		
				</ul>
			</div>
			<?php
		}
		echo $after_widget;
	}

	// Back-end widget form.
	public function form( $instance ) {
		
		
				
		if ( isset( $instance[ 'title' ] ) ) { $title = $instance[ 'title' ]; }
		else { $title = __( 'WvW Score', 'text_domain' ); }
		$sample_dropdown = esc_attr( $instance[ 'sample_dropdown' ] );
		$lang = esc_attr( $instance[ 'lang' ] );
		?>
		<p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>		
		<p>
			<label for="<?php echo $this->get_field_id('lang'); ?>"><?php _e( 'Language:' ); ?> <span style="font-size: 11px; font-style: italic;">(English only temporarily)</span></label>
			<select name="<?php echo $this->get_field_name('lang'); ?>" id="<?php echo $this->get_field_id('lang'); ?>" class="widefat">
			<?php
			//$languages = array(en, fr, de, es);
			$languages = array(en);
			foreach($languages as $valor ) {			
				?><option value="<?php echo $valor; ?>"<?php selected( $instance['lang'], $valor ); ?>><?php _e( $valor, 'dxbase' ); ?></option><?php	
			} 	
			?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('sample_dropdown'); ?>"><?php _e( 'World:' ); ?></label>
			<select name="<?php echo $this->get_field_name('sample_dropdown'); ?>" id="<?php echo $this->get_field_id('sample_dropdown'); ?>" class="widefat">
			<?php
			$url = file_get_contents(dirname(__FILE__).'/world_names.json');
			$arr = json_decode($url,true);
			foreach($arr as $valor) {				
				?><option value="<?php echo $valor['id']; ?>"<?php selected( $instance['sample_dropdown'], $valor['id'] ); ?>><?php _e( $valor['name'], 'dxbase' ); ?></option><?php			  
			} 
			?>			
			</select>
		</p>
		<?php $cachedir = dirname(__FILE__).'/cache'; if (substr(decoct(fileperms($cachedir)),2) != '777') : ?>			
			<p style="background: red; color: #fff; padding: 5px; margin-top: 20px;"><b>IMPORTANT:</b> To reduce load time you should look at the permissions on the folder 'cache' in the plugin directory `/wp-content/plugins/guild-wars-2-wvw-matchups/cache`. You have to give write permissions (777).</p>
		<?php else : ?>
			<p style="background: green; color: #fff; padding: 5px; margin-top: 20px;"><b>ALL OK.</b> Correct permissions on the folder 'cache'.</p>
		<?php endif; ?>
		
		<?php
	}

	// Sanitize widget form values as they are saved.
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['sample_dropdown'] = strip_tags($new_instance['sample_dropdown']);
		$instance['lang'] = strip_tags($new_instance['lang']);
		return $instance;
	}

} // class wvw_matchups_widget

// register wvw_matchups_widget
add_action( 'widgets_init', function() { register_widget( 'wvw_matchups_widget' ); } );