<?php

if ( ! class_exists( 'SIMPLE_QTIPS_Admin' ) ) {

/**
 * So that themes and other plugins can customise the text domain, the SIMPLE_QTIPS_Admin should
 * not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @author Jason Conroy <jason@findingsimple.com>
 * @package SIMPLE QTIPS
 * @since 1.0
 */
function simple_initialize_qtips_admin() {
	SIMPLE_QTIPS_Admin::init();
}
add_action( 'init', 'simple_initialize_qtips_admin', -1 );


class SIMPLE_QTIPS_Admin {

	public static function init() {  

		/* create custom plugin settings menu */
		add_action( 'admin_menu',  __CLASS__ . '::simple_qtips_create_menu' );

	}

	public static function simple_qtips_create_menu() {

		//create new top-level menu
		add_options_page( 'qTips Settings', 'qTips', 'administrator', 'simple_qtips', __CLASS__ . '::simple_qtips_settings_page' );

		//call register settings function
		add_action( 'admin_init',  __CLASS__ . '::register_mysettings' );

	}


	public static function register_mysettings() {
	
		$page = 'simple_qtips-settings'; 

		// General settings
		
		add_settings_section( 
			'simple_qtips-general', 
			'General Settings',
			__CLASS__ . '::simple_qtips_general_callback',
			$page
		);
		
		add_settings_field(
			'simple_qtips-attribute',
			'Attribute',
			__CLASS__ . '::simple_qtips_attribute_callback',
			$page,
			'simple_qtips-general'
		);
		
		add_settings_field(
			'simple_qtips-style',
			'Style',
			__CLASS__ . '::simple_qtips_style_callback',
			$page,
			'simple_qtips-general'
		);
		
		add_settings_field(
			'simple_qtips-custom-css-class',
			'Custom CSS class to apply to qTips',
			__CLASS__ . '::simple_qtips_custom_css_class_callback',
			$page,
			'simple_qtips-general'
		);
		
		add_settings_field(
			'simple_qtips-toggle-shadow',
			'Toggle Shadow',
			__CLASS__ . '::simple_qtips_toggle_shadow_callback',
			$page,
			'simple_qtips-general'
		);
		
		add_settings_field(
			'simple_qtips-hide',
			'Hide',
			__CLASS__ . '::simple_qtips_hide_callback',
			$page,
			'simple_qtips-general'
		);

		// Includes settings
		
		add_settings_section( 
			'simple_qtips-includes', 
			'CSS and JS Includes',
			__CLASS__ . '::simple_qtips_includes_callback',
			$page
		);
		
		add_settings_field(
			'simple_agls-toggle-css-include',
			'Toggle CSS enqueue in Head',
			__CLASS__ . '::simple_qtips_toggle_css_include_callback',
			$page,
			'simple_qtips-includes'
		);
		
		add_settings_field(
			'simple_agls-toggle-js-include',
			'Toggle JS enqueue in Head',
			__CLASS__ . '::simple_qtips_toggle_js_include_callback',
			$page,
			'simple_qtips-includes'
		);

		//register our settings
		
		register_setting( $page, 'simple_qtips-attribute' );
		register_setting( $page, 'simple_qtips-style' );
		register_setting( $page, 'simple_qtips-custom-css-class' );
		register_setting( $page, 'simple_qtips-toggle-shadow' );
		register_setting( $page, 'simple_qtips-hide' );
		
		register_setting( $page, 'simple_qtips-toggle-css-include' );
		register_setting( $page, 'simple_qtips-toggle-js-include' );

	}

	public static function simple_qtips_settings_page() {
	
		$page = 'simple_qtips-settings'; 
	
	?>
	<div class="wrap">
	
		<div id="icon-options-general" class="icon32"><br /></div><h2>SIMPLE-QTIPS Settings</h2>
		
		<?php settings_errors(); ?>
	
		<form method="post" action="options.php">
			
			<?php settings_fields( $page ); ?>
			
			<?php do_settings_sections( $page ); ?>
		
			<p class="submit">
				<input type="submit" class="button-primary" value="Save Changes" />
			</p>
		
		</form>
		
	</div>
	
	<?php 
	} 
	
	// General Settings Callbacks

	public static function simple_qtips_general_callback() {
		
		//do nothing
		
	}
	
	public static function simple_qtips_attribute_callback() {
	
		echo '<input name="simple_qtips-attribute" type="text" id="simple_qtips-attribute" class="regular-text" value="'. esc_attr( get_option('simple_qtips-attribute') ) . '"  />';
		
	}

	public static function simple_qtips_style_callback() {
		
		$selected = ( get_option('simple_qtips-style') ) ? esc_attr( get_option('simple_qtips-style') ) : 'ui-tooltip';
		
		echo '<select name="simple_qtips-style">';
		
		foreach ( SIMPLE_QTIPS_Admin::$classes as $class )  :
		
			echo '<option value="' . $class . '"';
			 if ( $class == $selected ) echo ' selected="selected"';
			echo ' >' . $class . '</option>';
		
		endforeach;
		
		echo '</select>';
		
	}

	public static function simple_qtips_custom_css_class_callback() {
	
		echo '<input name="simple_qtips-custom-css-class" type="text" id="simple_qtips-custom-css-class" class="regular-text" value="'. esc_attr( get_option('simple_qtips-custom-css-class') ) . '"  /> Alternatively use a custom css class';
		
	}
	
	public static function simple_qtips_toggle_shadow_callback() {
	
		echo '<input name="simple_qtips-toggle-shadow" id="simple_qtips-toggle-shadow" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_qtips-toggle-shadow'), false ) . ' /> Show shadow around tooltip (uses CSS3)';
		
	}	

	public static function simple_qtips_hide_callback() {
		
		$selected = ( get_option('simple_qtips-hide') ) ? esc_attr( get_option('simple_qtips-hide') ) : 'unfocus';
		
		echo '<select name="simple_qtips-hide">';
		
		foreach ( SIMPLE_QTIPS_Admin::$hide_options as $hide_option )  :
		
			echo '<option value="' . $hide_option . '"';
			 if ( $hide_option == $selected ) echo ' selected="selected"';
			echo ' >' . $hide_option . '</option>';
		
		endforeach;
		
		echo '</select>';
		
	}
	
	// Includes Settings Callbacks
	
	public static function simple_qtips_includes_callback() {
		
		echo '<p>Use the checkboxes below to toggle whether or not include the minified qTips css and js. You may want to include within an existing stylesheet or js file for performance reasons.</p>';
		
	}

	public static function simple_qtips_toggle_css_include_callback() {
	
		echo '<input name="simple_qtips-toggle-css-include" id="simple_qtips-toggle-css-include" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_qtips-toggle-css-include'), false ) . ' /> Do <strong>not</strong> include CSS in <code>&lt;head&gt;</code>';
		
	}
	
	public static function simple_qtips_toggle_js_include_callback() {
	
		echo '<input name="simple_qtips-toggle-js-include" id="simple_qtips-toggle-js-include" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_qtips-toggle-js-include'), false ) . ' /> Do <strong>not</strong> include JS before <code>&lt;/body&gt;</code>';
		
	}

	/**
	 * Default CSS Classes
	 *
	 */	
	public static $classes = array( 
		'ui-tooltip',
		'ui-tooltip-plain',
		'ui-tooltip-light',
		'ui-tooltip-dark',
		'ui-tooltip-red',
		'ui-tooltip-green',
		'ui-tooltip-blue' 
  	);
  	
	/**
	 * qTips Hide options
	 *
	 */	
	public static $hide_options = array( 
		'unfocus',
		'false'
  	);


}

}


