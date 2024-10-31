<?php
/**
 * points.php
 *
 * Copyright (c) 2011,2017 Antonio Blanco http://www.eggemplo.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Antonio Blanco (eggemplo)
 * @package points
 * @since points 1.0
 *
 * Plugin Name: Points
 * Plugin URI: http://www.eggemplo.com/plugins/points
 * Description: Points loyalty system.
 * Version: 1.1.4
 * Author: eggemplo
 * Author URI: http://www.eggemplo.com
 * Text Domain: points
 * Domain Path: /languages
 * License: GPLv3
 */

define( 'POINTS_FILE', __FILE__ );
define( 'POINTS_PLUGIN_BASENAME', plugin_basename( POINTS_FILE ) );

if ( !defined( 'POINTS_CORE_DIR' ) ) {
	define( 'POINTS_CORE_DIR', WP_PLUGIN_DIR . '/points' );
}
if ( !defined( 'POINTS_CORE_LIB' ) ) {
	define( 'POINTS_CORE_LIB', POINTS_CORE_DIR . '/lib/core' );
}

if ( !defined( 'POINTS_CORE_LIB_EXT' ) ) {
	define( 'POINTS_CORE_LIB_EXT', POINTS_CORE_DIR . '/lib/ext' );
}

if ( !defined( 'POINTS_CORE_VERSION' ) ) {
	define( 'POINTS_CORE_VERSION', '1.1.4' );
}

define( 'POINTS_PLUGIN_URL', plugin_dir_url( POINTS_FILE ) );

define( 'POINTS_DEFAULT_POINTS_LABEL', 'points' );

require_once ( POINTS_CORE_LIB . '/constants.php' );
require_once ( POINTS_CORE_LIB . '/class-points.php' );
require_once ( POINTS_CORE_LIB . '/class-points-database.php' );
require_once ( POINTS_CORE_LIB . '/class-points-shortcodes.php' );
require_once ( POINTS_CORE_LIB . '/class-points-widget-leaderboard.php' );
require_once ( POINTS_CORE_LIB . '/class-points-admin.php' );
require_once ( POINTS_CORE_LIB . '/class-points-table.php' );

require_once ( POINTS_CORE_LIB_EXT . '/class-points-wordpress.php' );


class Points_Plugin {

	private static $notices = array();

	public static function init() {

		load_plugin_textdomain( 'points', null, 'points/languages' );

		register_activation_hook( POINTS_FILE, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( POINTS_FILE, array( __CLASS__, 'deactivate' ) );
		register_uninstall_hook( POINTS_FILE, array( __CLASS__, 'uninstall' ) );

		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		add_action( 'widgets_init', array( __CLASS__,'points_widgets_init' ) );

	}

	public static function wp_init() {

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'points_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'points_admin_enqueue_scripts' ) );

		Points_Admin::init();

	}

	public static function points_admin_enqueue_scripts() {
		wp_register_style( 'points-admin-css', POINTS_PLUGIN_URL . 'css/points-admin.css' );
		wp_enqueue_style ('points-admin-css');

		wp_register_style('ui-datepicker',POINTS_PLUGIN_URL . 'css/jquery.datetimepicker.css', array(), '1.0');
		wp_enqueue_style( 'ui-datepicker' );

		// javascript
		wp_register_script('points-admin-script', POINTS_PLUGIN_URL . 'js/admin-scripts.js', array('jquery'),'1.0', true);
		wp_enqueue_script( 'datepicker', POINTS_PLUGIN_URL . 'js/jquery.datetimepicker.full.min.js', array( 'jquery', 'jquery-ui-core' ) );

		wp_enqueue_script('points-admin-script');
	}

	public static function points_enqueue_scripts() {
		wp_register_style( 'points-css', POINTS_PLUGIN_URL . 'css/points.css' );
		wp_enqueue_style ('points-css');
	}

	public static function points_widgets_init() {
		register_widget( 'Points_Widget' );
	}

	/**
	 * Plugin activation work.
	 * 
	 */
	public static function activate() {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		// create tables
		$points_users_table = Points_Database::points_get_table("users");
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$points_users_table'" ) != $points_users_table ) {
			$queries[] = "CREATE TABLE $points_users_table (
			point_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			points   BIGINT(20) DEFAULT 0,
			datetime     datetime NOT NULL,
			description  varchar(5000),
			ref_id       BIGINT(20) DEFAULT null,
			ip           int(10) unsigned default NULL,
			ipv6         decimal(39,0) unsigned default NULL,
			data         longtext default NULL,
			status       varchar(10) NOT NULL DEFAULT '" . POINTS_STATUS_ACCEPTED . "',
			type         varchar(32) NULL,
			PRIMARY KEY   (point_id)
			) $charset_collate;";
		}
		if ( !empty( $queries ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $queries );
		}
	}

	/**
	 * Plugin deactivation.
	 *
	 */
	public static function deactivate() {

	}

	/**
	 * Plugin uninstall. Delete database table.
	 *
	 */
	public static function uninstall() {

		//global $wpdb;
		//$wpdb->query('DROP TABLE IF EXISTS ' . Points_Database::points_get_table("users") );

	}

}
Points_Plugin::init();
