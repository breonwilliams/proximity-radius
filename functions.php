<?php

if ( ! function_exists( 'proximity_radius_bootstrap' ) ) {

	/**
	 * Initialize the plugin.
	 */
	function proximity_radius_bootstrap() {

		load_plugin_textdomain( 'proximity-radius', false, __DIR__ . '/languages' );

		// Register the Proximity Radius template
		proximity_radius_add_template(
			'proximity-radius-template.php',
			esc_html__( 'Proximity Radius', 'proximity-radius' )
		);

		// Add our template(s) to the dropdown in the admin
		add_filter(
			'theme_page_templates',
			function ( array $templates ) {
				return array_merge( $templates, proximity_radius_get_templates() );
			}
		);

		// Ensure our template is loaded on the front end
		add_filter(
			'template_include',
			function ( $template ) {

				if ( is_singular() ) {

					$assigned_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

					if ( proximity_radius_get_template( $assigned_template ) ) {

						if ( file_exists( $assigned_template ) ) {
							return $assigned_template;
						}

						// Allow themes to override plugin templates
						$file = locate_template( wp_normalize_path( '/proximity-radius/' . $assigned_template ) );
						if ( ! empty( $file ) ) {
							return $file;
						}

						// Fetch template from plugin directory
						$file = wp_normalize_path( plugin_dir_path( __FILE__ ) . '/templates/' . $assigned_template );
						if ( file_exists( $file ) ) {
							return $file;
						}
					}
				}

				return $template;

			}
		);

	}
}

if ( ! function_exists( 'proximity_radius_get_templates' ) ) {

	/**
	 * Get all registered templates.
	 *
	 * @return array
	 */
	function proximity_radius_get_templates() {
		return (array) apply_filters( 'proximity_radius_templates', array() );
	}
}

if ( ! function_exists( 'proximity_radius_get_template' ) ) {

	/**
	 * Get a registered template.
	 *
	 * @param string $file Template file/path
	 *
	 * @return string|null
	 */
	function proximity_radius_get_template( $file ) {
		$templates = proximity_radius_get_templates();

		return isset( $templates[ $file ] ) ? $templates[ $file ] : null;
	}
}

if ( ! function_exists( 'proximity_radius_add_template' ) ) {

	/**
	 * Register a new template.
	 *
	 * @param string $file  Template file/path
	 * @param string $label Label for the template
	 */
	function proximity_radius_add_template( $file, $label ) {
		add_filter(
			'proximity_radius_templates',
			function ( array $templates ) use ( $file, $label ) {
				$templates[ $file ] = $label;

				return $templates;
			}
		);
	}
}

if ( ! function_exists( 'proximity_radius_register_admin_page' ) ) {

	/**
	 * Register the admin page.
	 */
	function proximity_radius_register_admin_page() {
		add_menu_page(
			esc_html__( 'Proximity Radius', 'proximity-radius' ),
			esc_html__( 'Proximity Radius', 'proximity-radius' ),
			'edit_posts',
			'proximity-radius',
			function () {
				require __DIR__ . '/pages/admin.php';
			},
			'dashicons-media-default'
		);
	}
}


function my_acf_init() {

	acf_update_setting('google_api_key', 'AIzaSyB1YvSmHJ8TtSUSCZFZbZlZwIcMk38l1uI');
}

add_action('acf/init', 'my_acf_init');


// returns longitude and latitude from a location
function YOUR_THEME_NAME_get_lat_and_lng($origin){
	$api_key = "AIzaSyB1YvSmHJ8TtSUSCZFZbZlZwIcMk38l1uI";
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($origin)."&key=".$api_key;
    $result_string = file_get_contents($url);
    $result = json_decode($result_string, true);
    $result1[]=$result['results'][0];
    $result2[]=$result1[0]['geometry'];
	$result3[]=$result2[0]['location'];
	return $result3[0];
}

// returns distance between two locations
function YOUR_THEME_NAME_get_distance($origin, $address_lat, $address_lng, $unit){

    // get lat and lng from provided location
    $origin_coords = YOUR_THEME_NAME_get_lat_and_lng($origin);
    $lat1 = $origin_coords['lat'];
    $lng1 = $origin_coords['lng'];

    // get lat and lng from the address field on the custom post type
	$lat2 = $address_lat;
    $lng2 = $address_lng;

    // calculate distance between locations
    $theta=$lng1-$lng2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    // adjust calculation depending on unit
    if ($unit == "K"){
        return ($miles * 1.609344);
    }
    else if ($unit =="N"){
        return ($miles * 0.8684);
    }
    else{
        return $miles;
    }
}
