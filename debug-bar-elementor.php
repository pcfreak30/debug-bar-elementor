<?php

/*
 * Plugin Name: Debug Bar Elementor
 * Description: Output debug information regarding elementor on a page such as elements and performance data
 * Version: 0.1.0
 * Author: Derrick Hammer
 * Author URI: https://derrickhammer.com
 * License: GPL3
*/

function debug_bar_elementor_php_upgrade_notice() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Opps! %s requires a minimum PHP version of 5.4.0. Your current version is: %s. Please contact your host to upgrade.</p>
	</div>', $info['Name'], PHP_VERSION
		)
	);
}

function debug_bar_elementor_init() {
	if ( version_compare( PHP_VERSION, '5.4.0' ) < 0 ) {
		add_action( 'admin_notices', 'debug_bar_elementor_php_upgrade_notice' );

		return;
	}

	if ( ! class_exists( '\Debug_Bar' ) ) {
		return;
	}
	add_filter( 'debug_bar_panels', 'debug_bar_elementor_register_panel' );
	add_action( 'debug_bar_enqueue_scripts', 'debug_bar_elementor_enqueue_scripts' );
}


function debug_bar_elementor_register_panel( $panels ) {
	require_once __DIR__ . '/class-debug-bar-elementor.php';
	$panels[] = new Debug_Bar_Elementor();

	return $panels;
}

function debug_bar_elementor_enqueue_scripts() {
	wp_enqueue_script( 'debug-bar-elementor', plugins_url( 'assets/js/main.js', __FILE__ ), [ 'jquery-ui-accordion' ], null, true );
	wp_enqueue_style( 'jquery-ui-base', plugins_url( 'assets/css/jquery-ui.css', __FILE__ )  );
	wp_enqueue_style( 'debug-bar-elementor', plugins_url( 'assets/css/main.css', __FILE__ ) );
}


add_action( 'plugins_loaded', 'debug_bar_elementor_init' );
