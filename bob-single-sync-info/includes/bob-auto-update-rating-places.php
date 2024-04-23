<?php

/**
 * Get all post IDs
 * @return array All post IDs
 */
function bob_get_all_place_post_ids() {
    return get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1, // Get all posts
        'fields' => 'ids',
    ));
}


/**
 * Make Request call to get GOOGLE rating data on single post
 */
function bob_auto_get_post_google_rating_info($post_id) {

    $place_id = get_field('place_info_booking_place_google_id', $post_id);

    // Return in $place_id is empty and prevent run the request in vain
    if (empty($place_id)) {
        return "";
    }

    $base_url = 'https://maps.googleapis.com/maps/api/place/details/json';
    $api_key = get_field('bob_google_api_key', 'option');

    // Define Google API params
    $params = array(
        'place_id' => $place_id,
        'key' => $api_key,
        'fields' => array(
            'rating',
            'user_ratings_total',
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

        // Format 'result' to get clean data
        if ($data && isset($data['result'])) {

            // Access the 'rating' and 'user_ratings_total' fields in data
            $result = $data['result'];

            // Return them as an array
            return array(
                'rating' => $result['rating'],
                'user_ratings_total' => $result['user_ratings_total'],
            );
        } else {
            return "";
        }
    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'Failed Place data request';
    }
}





/**
 * Make Request call to get TRIP ADVISOR rating data on single post
 */
function bob_auto_get_post_trip_advisor_rating_info($post_id) {

    $place_id = get_field('place_info_booking_place_trip_advisor_id', $post_id);

    // Return in $place_id is empty and prevent run the request in vain
    if (empty($place_id)) {
        return "";
    }

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

        // Format 'result' to get clean data
        if ($data && isset($data['rating']) && isset($data['num_reviews'])) {

            // Return them as an array
            return array(
                'rating' => $data['rating'],
                'num_reviews' => $data['num_reviews'],
            );
        } else {
            return "";
        }
    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'Failed Place data request';
    }
}





/* 
// bob_auto_get_post_serp_api_rating_info
*/
function bob_auto_get_post_serp_api_rating_info($post_id) {
    $place_id = get_field('place_info_booking_place_google_id', $post_id);

    // Return in $place_id is empty and prevent run the request in vain
    if (empty($place_id)) {
        return "";
    }

    $place_info_booking = get_field('place_info_booking', $post_id);
    $place_name         = $place_info_booking['place_name'];

    $place_geometry = get_field('place_geometry', $post_id);
    $latitude       = $place_geometry['latitude'];
    $longitude      = $place_geometry['longitude'];

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

        // Format 'result' to get clean data
        if (!empty($data)) {
            $price_range = isset($data['rate_per_night']['extracted_lowest']) ? $data['rate_per_night']['extracted_lowest'] : '';
            $hotel_class = isset($data['extracted_hotel_class']) ? $data['extracted_hotel_class'] : '';

            return array(
                'price_range' => $price_range,
                'hotel_class' => $hotel_class,
            );
        } else {
            return "";
        }
    } else {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'Failed Place data request';
        echo 'Error: ' . $error_message;
    }
};





/**
 * Get INTERNAL rating data on single post
 */
function bob_auto_get_post_internal_rating_info($post_id) {
    // Return them as an array
    return array(
        'rating' => get_field('best_of_bali_rating', $post_id),
        'num_reviews' => '1',
    );
}


/**
 * Auto update ALL PROVIDERS rating info and then make AGGREGATED RATING update in single post.
 */
function bob_auto_update_posts_rating_info() {

    // Obtain all POST IDs of places
    $post_ids = bob_get_all_place_post_ids();

    foreach ($post_ids as $post_id) {

        // Fetch updated rating info from providers
        $google_data = bob_auto_get_post_google_rating_info($post_id);
        $trip_advisor_data = bob_auto_get_post_trip_advisor_rating_info($post_id);
        $serp_api_data = bob_auto_get_post_serp_api_rating_info($post_id);

        // Update GOOGLE rating post meta
        if (!empty($google_data)) {
            update_field('place_info_booking_place_google_rating', $google_data['rating'], $post_id);
            update_field('place_info_booking_place_google_rating_totals', $google_data['user_ratings_total'], $post_id);
        }

        // Update TRIP ADVISOR rating post meta
        if (!empty($trip_advisor_data)) {
            update_field('place_info_booking_place_trip_advisor_rating', $trip_advisor_data['rating'], $post_id);
            update_field('place_info_booking_place_trip_advisor_rating_totals', $trip_advisor_data['num_reviews'], $post_id);
        }

        // Update SERP API rating post meta
        if (!empty($serp_api_data)) {
            update_field('place_info_booking_serp_api_hotel_class', $serp_api_data['hotel_class'], $post_id);

            if (!empty($serp_api_data['price_range'])) {
                update_field('place_info_booking_serpapi_price_range', $serp_api_data['price_range'], $post_id);
            }
        }

        // Run post update to trigger generation of AGGREGATED RATING value
        wp_update_post(array('ID' => $post_id));
    }
}


// /*
// Create Cron Job
//  */
if (!wp_next_scheduled('wcl_cron_activate_wget_auto_update_posts_event')) {
    wp_schedule_event(time(), 'weekly', 'wcl_cron_activate_wget_auto_update_posts_event');
}

add_action('wcl_cron_activate_wget_auto_update_posts_event', 'wcl_cron_activate_wget_auto_update_posts');



/*
wcl_cron_activate_wget_auto_update_posts
 */
function wcl_cron_activate_wget_auto_update_posts() {
    bob_auto_update_posts_rating_info();
}
