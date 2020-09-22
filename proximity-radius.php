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

require __DIR__ . '/functions.php';

add_action( 'plugins_loaded', 'proximity_radius_bootstrap' );
