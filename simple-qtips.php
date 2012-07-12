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


if ( ! class_exists( 'Simple_qTips' ) ) :

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
			'tooltip_title' => __( 'Tooltip Title' ),
			'tooltip_text'  => __( 'Tooltip Text' ),
		);

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles_and_scripts'), 100 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_styles_and_scripts' ) );

		add_action( 'admin_footer', array( __CLASS__, 'the_jquery_dialog_markup' ) );

		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'add_quicktag_button' ), 100 );

		add_filter( 'mce_external_plugins', array( __CLASS__, 'register_tinymce_plugin' ) );

		add_filter( 'mce_buttons', array( __CLASS__, 'register_tinymce_button' ) );

	}

	/**
	 * Add qtips2 scripts
	 *
	 * @since 1.0
	 */
	public static function enqueue_styles_and_scripts(){
		wp_enqueue_script( 'simple-qtips', plugins_url( '/js/jquery.qtip.min.js', fs_get_simple_qtips_plugin_file() ) ,'','',true );
		wp_enqueue_style( 'simple-qtips', plugins_url( '/css/jquery.qtip.min.css', fs_get_simple_qtips_plugin_file() ) );
	}

	/**
	 * Enqueues the necessary scripts and styles for the plugins
	 *
	 * @since 1.0
	 */
	public static function enqueue_admin_styles_and_scripts() {

		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

		wp_register_style( 'simple-qtips', plugins_url( '/css/simple-qtips.css', __FILE__ ) , false, '1.0' );
		wp_enqueue_style( 'simple-qtips' );

		wp_enqueue_script( 'simple-qtips', plugins_url( '/js/qtip-admin.js', __FILE__ ) );

		wp_localize_script( 'simple-qtips', 'qtipFields', self::$tooltip_fields );
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
	 * Adds a QuickTag button to the HTML editor.
	 *
	 * Compatible with WordPress 3.3 and newer.
	 *
	 * @see wp-includes/js/quicktags.dev.js -> qt.addButton()
	 * @since 1.0
	 */
	public static function add_quicktag_button() {
		// Only run the function on post edit screens
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ($screen->base != 'post')
				return;
		}
?>
<script type="text/javascript" charset="utf-8">
	QTags.addButton( 'simple_qtips_id', 'qTips', qt_simple_qtips );
	function qt_simple_qtips() {
		simple_qtips_caller = 'html';
		jQuery( "#simple-tips-dialog" ).dialog( "open" );
	}
</script>
<?php
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
		<label for="qtip-<?php echo $field_id; ?>" style="display: inline-block; width: 90%; margin: 2px;">
			<?php echo $field_label; ?>
			<input type="text" id="qtip-<?php echo $field_id; ?>" name="qtip-<?php echo $field_id; ?>" value=""  style="width: 75%; float: right;"/>
		</label>
		<?php endforeach; ?>

		</div><!-- #snippets-tabs -->
	</div><!-- #snippets-dialog -->
</div><!-- .hidden -->
<?php
	}

}

Simple_qTips::init();

endif;