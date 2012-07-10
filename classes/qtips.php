<?php
/**
 * Plugin Main Class.
 *
 * @package Simple Snippets
 * @author Johan Steen <artstorm at gmail dot com>
 * @since 1.0
 */
class Simple_qTips {

	// Constants
	const TINYMCE_PLUGIN_NAME = 'simple_qtips';

	// -------------------------------------------------------------------------

	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initializes the hooks for the plugin
	 */
	function init_hooks() {

		// Add TinyMCE button
		add_action('init', array(&$this, 'add_tinymce_button') );

		// Settings link on plugins list
		add_filter( 'plugin_action_links', array(&$this, 'plugin_action_links'), 10, 2 );
		
		// Options Page
		//add_action( 'admin_menu', array(&$this,'wp_admin') );

		//$this->create_shortcodes();

		// Adds the JS and HTML code in the header and footer for the jQuery
		// insert UI dialog in the editor
		add_action( 'admin_init', array(&$this,'enqueue_assets') );
		add_action( 'admin_head', array(&$this,'jquery_ui_dialog') );
		add_action( 'admin_footer', array(&$this,'add_jquery_ui_dialog') );
		
		global $wp_version;
		
		if ( version_compare($wp_version, '3.3', '>=') ) {
			add_action( 'admin_print_footer_scripts', 
						array(&$this,'add_quicktag_button'), 100 );
		} else {
			add_action( 'edit_form_advanced', array(&$this,'add_quicktag_button_pre33') );
			add_action( 'edit_page_form', array(&$this,'add_quicktag_button_pre33') );
		}
		
		// Add frontend scripts and styles
		add_action( 'wp_enqueue_scripts', array(&$this,'add_simple_qtips_styles'), 100 );
		add_action( 'wp_print_scripts', array(&$this,'add_simple_qtips_scripts'), 100 );
		
	}


	/**
	 * Quick link to the Post Snippets Settings page from the Plugins page.
	 *
	 * @return	Array with all the plugin's action links
	 */
	function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( dirname( fs_get_simple_qtips_plugin_file() ) . '/simple-qtips.php' ) ) {
			$links[] = '<a href="options-general.php?page=' . FS_QTIPS_OPTION_KEY . '">' . __( 'Settings', 'simple-qtips' ) . '</a>';
		 }
		return $links;
	}


	/**
	 * Enqueues the necessary scripts and styles for the plugins
	 *
	 * @since 1.0
	 */
	function enqueue_assets() {
	
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

		// Adds css for the jQuery UI dialog
		wp_register_style( 'simple-qtips', plugins_url( '/css/simple-qtips.css', fs_get_simple_qtips_plugin_file() ) , false, '1.0' );
		wp_enqueue_style( 'simple-qtips' );
		
	}
	
	/**
	 * Add qtips2 scripts
	 *
	 */
	function add_simple_qtips_scripts(){
	
		if (!is_admin()) {

			/* qTips2 */
			wp_register_script( 'simple-qtips', plugins_url( '/js/jquery.qtip.min.js', fs_get_simple_qtips_plugin_file() ) ,'','',true);
			wp_enqueue_script( 'simple-qtips' );
							
		}
		
	}
	
	/**
	 * Add qtips2 styles
	 *
	 */
	function add_simple_qtips_styles(){
	
		if (!is_admin()) {

			/* qTips2 Default Styles*/
			wp_register_style( 'simple-qtips', plugins_url( '/css/jquery.qtip.min.css', fs_get_simple_qtips_plugin_file() ) );
        	wp_enqueue_style( 'simple-qtips' );
							
		}
		
	}
	
	
	

	// -------------------------------------------------------------------------
	// WordPress Editor Buttons
	// -------------------------------------------------------------------------

	/**
	 * Add TinyMCE button.
	 *
	 * Adds filters to add custom buttons to the TinyMCE editor (Visual Editor)
	 * in WordPress.
	 *
	 * @since 1.0
	 */
	public function add_tinymce_button() {
		// Don't bother doing this stuff if the current user lacks permissions
		if ( !current_user_can('edit_posts') &&
			 !current_user_can('edit_pages') )
			return;

		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter('mce_external_plugins', 
						array(&$this, 'register_tinymce_plugin') );
			add_filter('mce_buttons',
						array(&$this, 'register_tinymce_button') );
		}
	}

	/**
	 * Register TinyMCE button.
	 *
	 * Pushes the custom TinyMCE button into the array of with button names.
	 * 'separator' or '|' can be pushed to the array as well. See the link
	 * for all available TinyMCE controls.
	 *
	 * @see		wp-includes/class-wp-editor.php
	 * @link	http://www.tinymce.com/wiki.php/Buttons/controls
	 * @since 1.0
	 *
	 * @param	array	$buttons	Filter supplied array of buttons to modify
	 * @return	array				The modified array with buttons
	 */
	public function register_tinymce_button( $buttons ) {
		array_push( $buttons, 'separator', self::TINYMCE_PLUGIN_NAME );
		return $buttons;
	}

	/**
	 * Register TinyMCE plugin.
	 *
	 * Adds the absolute URL for the TinyMCE plugin to the associative array of
	 * plugins. Array structure: 'plugin_name' => 'plugin_url'
	 *
	 * @see		wp-includes/class-wp-editor.php
	 * @since 1.0
	 *
	 * @param	array	$plugins	Filter supplied array of plugins to modify
	 * @return	array				The modified array with plugins
	 */
	public function register_tinymce_plugin( $plugins ) {
		// Load the TinyMCE plugin, editor_plugin.js, into the array
		$plugins[self::TINYMCE_PLUGIN_NAME] = plugins_url( '/tinymce/editor_plugin.js?ver=1.9', fs_get_simple_qtips_plugin_file() );

		return $plugins;
	}

	/**
	 * Adds a QuickTag button to the HTML editor.
	 *
	 * Compatible with WordPress 3.3 and newer.
	 *
	 * @see			wp-includes/js/quicktags.dev.js -> qt.addButton()
	 * @since 1.0
	 */
	public function add_quicktag_button() {
		// Only run the function on post edit screens
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ($screen->base != 'post')
				return;
		}

		echo "\n<!-- START: Add QuickTag button for Simple qTips -->\n";
		?>
		<script type="text/javascript" charset="utf-8">
			QTags.addButton( 'simple_qtips_id', 'qTips', qt_simple_qtips );
			function qt_simple_qtips() {
				simple_qtips_caller = 'html';
				jQuery( "#simple-tips-dialog" ).dialog( "open" );
			}
		</script>
		<?php
		echo "\n<!-- END: Add QuickTag button for Simple qTips -->\n";
	}


	/**
	 * Adds a QuickTag button to the HTML editor.
	 *
	 * Used when running on WordPress lower than version 3.3.
	 *
	 * @see			wp-includes/js/quicktags.dev.js
	 * @since 1.0
	 * @deprecated	Since 1.8.6
	 */
	function add_quicktag_button_pre33() {
		// Only run the function on post edit screens
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ($screen->base != 'post')
				return;
		}

		echo "\n<!-- START: Simple qTips QuickTag button -->\n";
		?>
		<script type="text/javascript" charset="utf-8">
		// <![CDATA[
			//edButton(id, display, tagStart, tagEnd, access, open)
			edbuttonlength = edButtons.length;
			edButtons[edbuttonlength++] = new edButton('ed_simpleqtips', 'Simple qTips', '', '', '', -1);
		   (function(){
				  if (typeof jQuery === 'undefined') {
						 return;
				  }
				  jQuery(document).ready(function(){
						 jQuery("#ed_toolbar").append('<input type="button" value="Simple qTips" id="ed_simpleqtips" class="ed_button" onclick="edOpenSimpleqTips(edCanvas);" title="Simple qTips" />');
				  });
			}());
		// ]]>
		</script>
		<?php
		echo "\n<!-- END: Simple qTips QuickTag button -->\n";
	}


	// -------------------------------------------------------------------------
	// JavaScript / jQuery handling for the post editor
	// -------------------------------------------------------------------------

	/**
	 * jQuery control for the dialog and Javascript needed to insert snippets into the editor
	 *
	 * @since 1.0
	 */
	public function jquery_ui_dialog() {
		// Only run the function on post edit screens
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ($screen->base != 'post')
				return;
		}

		echo "\n<!-- START: Simple qTips jQuery UI and related functions -->\n";
		echo "<script type='text/javascript'>\n";
		
		?>
		
		jQuery(document).ready(function($){
			
			$(function() {
				$( "#simple-qtips-dialog" ).dialog({
					autoOpen: false,
					modal: true,
					dialogClass: 'wp-dialog',
					buttons: {
						Cancel: function() {
							$( this ).dialog( "close" );
						}
					},
					width: 500,
				});
			});
		});

// Global variables to keep track on the canvas instance and from what editor
// that opened the Simple qTips popup.
var simple_qtips_canvas;
var simple_qtips_caller = '';

/**
 * Used in WordPress lower than version 3.3.
 * Not used anymore starting with WordPress version 3.3.
 * Called from: add_quicktag_button_pre33()
 */
function edOpenSimpleqTips(myField) {
		simple_qtips_canvas = myField;
		simple_qtips_caller = 'html_pre33';
		jQuery( "#simple-qtips-dialog" ).dialog( "open" );
};
<?php
		echo "</script>\n";
		echo "\n<!-- END: Simple qTips jQuery UI and related functions -->\n";
	}

	/**
	 * Build jQuery UI Window.
	 *
	 * Creates the jQuery for Post Editor popup window, its snippet tabs and the
	 * form fields to enter variables.
	 *
	 * @since 1.0
	 */
	public function add_jquery_ui_dialog() {
		// Only run the function on post edit screens
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ($screen->base != 'post')
				return;
		}

		echo "\n<!-- START: Simple qTips UI Dialog -->\n";
		// Setup the dialog divs
		echo "<div class=\"hidden\">\n";
		echo "\t<div id=\"simple-qtips-dialog\" title=\"Simple qTips\">\n";
		
		echo "do stuff here";
		
		echo "\t</div><!-- #post-snippets-dialog -->\n";
		echo "</div><!-- .hidden -->\n";

		echo "<!-- END: Post Snippets UI Dialog -->\n\n";
	}


}
