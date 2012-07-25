<?php
/*
Plugin Name: Simple qTips
Plugin URI: http://plugins.findingsimple.com
Description: Easily insert qTips.
Version: 1.0
Author: Finding Simple (Jason Conroy & Brent Shepherd)
Author URI: http://findingsimple.com
License: GPL2
*/
/*
Copyright 2012  Finding Simple  (email : plugins@findingsimple.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once dirname( __FILE__ ) . '/simple-qtips-admin.php';

if ( ! class_exists( 'Simple_qTips' ) ) :

/**
 * So that themes and other plugins can customise the text domain, the Simple_qTips
 * should not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @author Jason Conroy <jason@findingsimple.com>
 * @package Simple qTips
 * @since 1.0
 */
function initialize_qtips(){
	Simple_qTips::init();
}
add_action( 'init', 'initialize_qtips', -1 );

/**
 * Plugin Main Class.
 *
 * @package Simple qTips
 * @since 1.0
 */
class Simple_qTips {

	const TINYMCE_PLUGIN_NAME = 'simple_qtips';

	private static $tooltip_fields;

	public static function init() {

		self::$tooltip_fields = array(
			'link_text'     => __( 'Link Text' ),
			'link_title'    => __( 'Link Title' ),
			'link_url'      => __( 'Link URL' ),
			'tooltip_text'  => __( 'Tooltip Text' )
		);

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles_and_scripts'), 100 );
		
		add_action( 'wp_footer', array( __CLASS__, 'go_go_qtips'), 100 );
		
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_styles_and_scripts' ) );

		add_action( 'admin_footer', array( __CLASS__, 'the_jquery_dialog_markup' ) );

		add_filter( 'mce_external_plugins', array( __CLASS__, 'register_tinymce_plugin' ) );

		add_filter( 'mce_buttons', array( __CLASS__, 'register_tinymce_button' ) );
		
		add_shortcode( 'qtip', array( __CLASS__, 'shortcode_qtip') );

	}

	/**
	 * Add qtips2 scripts
	 *
	 * @since 1.0
	 */
	public static function enqueue_styles_and_scripts(){
		
		if ( !is_admin() ) {
		
			if ( get_option('simple_qtips-toggle-js-include') != 1 )
				wp_enqueue_script( 'simple-qtips', plugins_url( '/js/jquery.qtip.min.js', __FILE__ ) ,'jquery','2.0',true );
			
			if ( get_option('simple_qtips-toggle-css-include') != 1 )
				wp_enqueue_style( 'simple-qtips', plugins_url( '/css/jquery.qtip.min.css', __FILE__ ) );
		
		}
		
	}

	/**
	 * Enqueues the necessary scripts and styles for the plugins
	 *
	 * @since 1.0
	 */
	public static function enqueue_admin_styles_and_scripts() {
		
		global $pagenow;
		
		if ( is_admin() && $pagenow == 'post-new.php' || $pagenow == 'post.php' ) {

			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
	
			wp_register_style( 'simple-qtips', plugins_url( '/css/simple-qtips.css', __FILE__ ) , false, '1.0' );
			wp_enqueue_style( 'simple-qtips' );
	
			wp_enqueue_script( 'simple-qtips', plugins_url( '/js/qtip-admin.js', __FILE__ ) );
	
			wp_localize_script( 'simple-qtips', 'qtipFields', self::$tooltip_fields );
		
		}
		
	}

	/**
	 * Register TinyMCE button.
	 *
	 * @see wp-includes/class-wp-editor.php
	 * @link http://www.tinymce.com/wiki.php/Buttons/controls
	 * @since 1.0
	 *
	 * @param array $buttons Filter supplied array of buttons to modify
	 * @return array The modified array with buttons
	 */
	public static function register_tinymce_button( $buttons ) {

		array_push( $buttons, 'separator', self::TINYMCE_PLUGIN_NAME );

		return $buttons;
	}

	/**
	 * Register TinyMCE plugin.
	 *
	 * Adds the absolute URL for the TinyMCE plugin to the associative array of plugins. Array structure: 'plugin_name' => 'plugin_url'
	 *
	 * @see		wp-includes/class-wp-editor.php
	 * @since 1.0
	 *
	 * @param	array $plugins Filter supplied array of plugins to modify
	 * @return	array The modified array with plugins
	 */
	public static function register_tinymce_plugin( $plugins ) {

		$plugins[self::TINYMCE_PLUGIN_NAME] = plugins_url( '/tinymce/editor_plugin.js?ver=1.0', __FILE__ );

		return $plugins;
	}

	/**
	 * Build jQuery UI Window.
	 *
	 * Creates the jQuery for Post Editor popup window, its snippet tabs and the
	 * form fields to enter variables.
	 *
	 * @since 1.0
	 */
	public static function the_jquery_dialog_markup() {

		$screen = get_current_screen();

		if ($screen->base != 'post')
				return;
?>
<div class="hidden">
	<div id="simple-qtips-dialog" title="Insert qTip">
		<div id="qtip-details" style="margin: 1em;">

		<?php foreach ( self::$tooltip_fields as $field_id => $field_label ) : ?>
		<?php if ( $field_id != 'tooltip_text' ) { ?>
		<label for="qtip-<?php echo $field_id; ?>" style="display: inline-block; width: 90%; margin: 2px;">
			<?php echo $field_label; ?>
			<input type="text" id="qtip-<?php echo $field_id; ?>" name="qtip-<?php echo $field_id; ?>" value=""  style="width: 75%; float: right;"/>
		</label>
		<?php } else { ?>
		<label for="qtip-<?php echo $field_id; ?>" style="display: inline-block; width: 90%; margin: 2px;">
			<?php echo $field_label; ?>
			<textarea id="qtip-<?php echo $field_id; ?>" name="qtip-<?php echo $field_id; ?>" style="width: 75%; float: right;"></textarea>
		</label>		
		<?php } ?>
		<?php endforeach; ?>

		</div><!-- #snippets-tabs -->
	</div><!-- #snippets-dialog -->
</div><!-- .hidden -->
<?php
	}
	
	
	/**
	 * Go Go qTips!
	 *
	 * @since 1.0
	 */
	public static function go_go_qtips() {

	$selector = ( get_option('simple_qtips-selector') ) ? get_option('simple_qtips-selector') : '.tooltip';

	$style = ( get_option('simple_qtips-custom-css-class') ) ? get_option('simple_qtips-custom-css-class') : get_option('simple_qtips-style');
	$css3_shadow = ( get_option('simple_qtips-toggle-shadow') == 1 ) ? 'ui-tooltip-shadow' : '';
	$hide = ( get_option('simple_qtips-hide') == 'false' ) ? false : ( get_option('simple_qtips-hide') == null ) ? 'unfocus' : get_option('simple_qtips-hide') ;

	$my = ( get_option('simple_qtips-my-position') ) ? get_option('simple_qtips-my-position')  : 'top left' ;
	$at = ( get_option('simple_qtips-at-position') ) ? get_option('simple_qtips-at-position')  : 'bottom right' ;

	?>
<script type="text/javascript">
jQuery('<?php echo $selector ;?>').qtip({
	content: {
		attr: 'data-qtip-content',
		title: {
			text: function(api) {
				return jQuery(this).attr('title');
			},
			button: 'Close'
		}
	},
	position: {
		my: '<?php echo $my; ?>',  // Position my top left...
		at: '<?php echo $at; ?>' // at the bottom right of...
	},
	hide: {
		event: '<?php echo $hide; ?>'
	},
	style: {
		classes: '<?php echo $style . " " . $css3_shadow ; ?>'
	}
});
</script>
<?php
	
	}

	/**
	 * Build qtip shortcode.
	 *
	 * @since 1.0
	 *	
	 * Required arguments:
	 *  - link_text 
	 *  - link_title
	 *  - link_url
	 *  - tooltip_text
	 *
	 * If required arguments are missin there is no output
	 *
	 * @since 1.0
	 * @author Jason Conroy <jason@findingsimple.com>
	 * @package SIMPLE-QTIPS
	 *
	 */
	 
	public static function shortcode_qtip($atts, $content = null) {
	
		extract( shortcode_atts( 
			array(	'link_text' => '',
					'link_title' => '',
					'link_url' => '',
					'tooltip_text' => ''
			) , $atts)
		);
		
		$content = '';
		
		if ( $link_text && $link_title && $link_url && $tooltip_text ) {

			$selector = ( get_option('simple_qtips-selector') ) ? get_option('simple_qtips-selector') : '.tooltip';
			$selector = trim( $selector, ".");
			$selector = trim( $selector, "#");
			
			$content .= '<a ';
			$content .= 'href="' . $link_url . '" ';
			$content .= 'title="' . $link_title . '" ';
			$content .= 'class="' . $selector . '" ';
			$content .= 'data-qtip-content="' . $tooltip_text . '" ';
			$content .= '>';
			$content .= $link_text;
			$content .= '</a>';
		
		}
	
		return self::qtips_remove_wpautop($content);
	
	}

	/**
	 * Replaces WP autop formatting 
	 *
	 * @since 1.0
	 * @author Jason Conroy <jason@findingsimple.com>
	 * @package SIMPLE-QTIPS
	 */
	public static function qtips_remove_wpautop($content) { 
		$content = do_shortcode( shortcode_unautop( $content ) ); 
		$content = preg_replace( '#^<\/p>|^<br \/>|<p>$#', '', $content);
		return $content;
	}	
	
	

}

endif;