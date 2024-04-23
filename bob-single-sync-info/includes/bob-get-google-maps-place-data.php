<?php

// Enqueue required JS file
function bob_enqueue_ajax_google_maps_place_data() {
    wp_enqueue_script('bob-ajax-google-maps-place-data', plugin_dir_url(__FILE__) . '/js/bob-ajax-google-maps-place-data.js', array('jquery'), null, true);
}

add_action('admin_enqueue_scripts', 'bob_enqueue_ajax_google_maps_place_data');


// Enqueue styles
function bob_enqueue_style_google_maps_place_data() {
    wp_enqueue_style('bob-style-google-maps-place-data', plugin_dir_url(__FILE__) . '/css/bob-style-google-maps-place-data.css');
}

add_action('admin_enqueue_scripts', 'bob_enqueue_style_google_maps_place_data');



/**
 * Make Request call to get Google Maps place data
 */
function bob_get_google_maps_place_data() {
    
    $place_id = $_POST['place_google_id'];
    $base_url = 'https://maps.googleapis.com/maps/api/place/details/json';
    $api_key = get_field('bob_google_api_key', 'option');

    // Define Google API params
    $params = array(
        'place_id' => $place_id,
        'key' => $api_key,
        'fields' => array(
            'name',
            'adr_address',
            'url',
            'opening_hours',
            'rating',
            'price_level',
            "user_ratings_total",
            "geometry",
        )
    );

    // Format param 'fields' in separate comma list
    $params['fields'] = implode(',', $params['fields']);

    // Add params to base URL
    $full_get_url = add_query_arg($params, $base_url);

    // Make request
    $response = wp_remote_get($full_get_url);

    if (is_array($response) && !is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        wp_send_json($data);

    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'Failed Place data request';
        echo 'Error: ' . $error_message;
    }
}

add_action('wp_ajax_bob_get_google_maps_place_data', 'bob_get_google_maps_place_data');
add_action('wp_ajax_nopriv_bob_get_google_maps_place_data', 'bob_get_google_maps_place_data');
