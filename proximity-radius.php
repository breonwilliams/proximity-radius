<?php
/*
Plugin Name: Proximity Radius Plugin
Plugin URI: http://breonwilliams.com
Description: adds proximity radius search template
Version: 1.1
Author: Breon Williams
Author URI: http://breonwilliams.com
License: GPL2
*/

function wpse_load_plugin_css() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style( 'style1', $plugin_url . 'assets/css/main.css' );
}
add_action( 'wp_enqueue_scripts', 'wpse_load_plugin_css' );

require __DIR__ . '/functions.php';

add_action( 'plugins_loaded', 'proximity_radius_bootstrap' );
