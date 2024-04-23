<?php

// Enqueue required JS file
function bob_enqueue_ajax_trip_advisor_place_data() {
    wp_enqueue_script('bob-ajax-trip-advisor-place-data', plugin_dir_url(__FILE__) . '/js/bob-ajax-trip-advisor-place-data.js', array('jquery'), null, true);
}

add_action('admin_enqueue_scripts', 'bob_enqueue_ajax_trip_advisor_place_data');


// Enqueue styles
function bob_enqueue_style_trip_advisor_place_data() {
    wp_enqueue_style('bob-style-trip-advisor-place-data', plugin_dir_url(__FILE__) . '/css/bob-style-trip-advisor-place-data.css');
}

add_action('admin_enqueue_scripts', 'bob_enqueue_style_trip_advisor_place_data');


/**
 * Make Request call to get Trip Advisor place data
 */
function bob_get_trip_advisor_place_data() {

    $place_id = $_POST['place_trip_advisor_id'];
    $base_url = "https://api.content.tripadvisor.com/api/v1/location/{$place_id}/details";
    $api_key = get_field('bob_trip_advisor_api_key', 'option');

    // Define Trip Advisor REQUIRED API params (We are getting all data, define values to work in JS file).
    $params = array(
        'key' => $api_key,
        'language' => 'en',
        'currency' => 'USD'
    );

    // Add params to base URL
    $full_get_url = add_query_arg($params, $base_url);

    // Set HTTP Referrer
    $referrer = get_site_url();

    // Make request with HTTP Referrer
    $response = wp_remote_get(
        $full_get_url,
        array(
            'headers' => array(
                'Referer' => $referrer
            )
        )
    );

    if (is_array($response) && !is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        wp_send_json($data);
    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'Failed Place data request';
        echo 'Error: ' . $error_message;
    }
}

add_action('wp_ajax_bob_get_trip_advisor_place_data', 'bob_get_trip_advisor_place_data');
add_action('wp_ajax_nopriv_bob_get_trip_advisor_place_data', 'bob_get_trip_advisor_place_data');
