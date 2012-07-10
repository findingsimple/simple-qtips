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

if ( ! defined( 'FS_QTIPS_OPTION_KEY' ) )
	define( 'FS_QTIPS_OPTION_KEY', 'simple_qtips' );

require plugin_dir_path( __FILE__ ) . 'classes/qtips.php';

//if ( is_admin() ) {
//	require plugin_dir_path( __FILE__ ) . 'classes/settings.php';
//}

function fs_init_simple_qtips(){

	global $simple_qtips;

	$simple_qtips = new Simple_qTips();

}
add_action( 'plugins_loaded', 'fs_init_simple_qtips' );


/**
 * Get __FILE__ with no symlinks.
 *
 * @since 1.0
 * @return The __FILE__ constant without resolved symlinks.
 */
function fs_get_simple_qtips_plugin_file(){
	return __FILE__;
}
