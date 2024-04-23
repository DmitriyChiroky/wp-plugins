<?php

// Enqueue required JS file
function bob_enqueue_ajax_serp_api_place_data() {
    wp_enqueue_script('bob-ajax-serp-api-place-data', plugin_dir_url(__FILE__) . '/js/bob-ajax-serp-api-place-data.js', array('jquery'), null, true);
}

add_action('admin_enqueue_scripts', 'bob_enqueue_ajax_serp_api_place_data');


// Enqueue styles
function bob_enqueue_style_serp_api_place_data() {
    wp_enqueue_style('bob-style-serp-api-place-data', plugin_dir_url(__FILE__) . '/css/bob-style-serp-api-place-data.css');
}

add_action('admin_enqueue_scripts', 'bob_enqueue_style_serp_api_place_data');





/* 
// Make Request call to get Serp Api place data
*/
function bob_get_serp_api_place_data() {
    $place_name = $_POST['place_name'];
    $latitude   = $_POST['latitude'];
    $longitude  = $_POST['longitude'];

    $currentDate = date("Y-m-d");
    $nextDate    = date("Y-m-d", strtotime("+1 day", strtotime($currentDate)));

    $api_key = get_field('bob_serp_api_key', 'option');

    $params = array(
        "engine"         => "google_hotels",
        "q"              => $place_name,
        "check_in_date"  => $currentDate,
        "check_out_date" => $nextDate,
        "currency"       => "USD",
        "gl"             => "us",
        "hl"             => "en",
        "api_key"        => $api_key
    );

    $url = "https://serpapi.com/search.json?" . http_build_query($params);

    // Make request using WordPress HTTP API
    $response = wp_remote_get($url);

    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = serp_api_place_data_processing($body, $place_name, $latitude, $longitude);
        wp_send_json($data);
    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'Failed Place data request';
        echo 'Error: ' . $error_message;
    }
};
add_action('wp_ajax_bob_get_serp_api_place_data', 'bob_get_serp_api_place_data');
add_action('wp_ajax_nopriv_bob_get_serp_api_place_data', 'bob_get_serp_api_place_data');






/* 
serp_api_place_data_processing
 */
function serp_api_place_data_processing($json, $place_name, $latitude, $longitude) {
    if (empty($json)) {
        return '';
    }

    $data = '';
    $hotels = json_decode($json, true);

    foreach ($hotels['properties'] as $t_place) {
        $t_place_name            = $t_place['name'];
        $t_place_gps_coordinates = $t_place['gps_coordinates'];
        $t_place_latitude        = $t_place_gps_coordinates['latitude'];
        $t_place_longitude       = $t_place_gps_coordinates['longitude'];

        // Check if hotel is same
        if ($place_name == $t_place_name || ((round($latitude, 5) == round($t_place_latitude, 5)) && (round($longitude, 5) == round($t_place_longitude, 5)))) {
            $data = $t_place;
            break;
        }
    }

    return $data;
}
