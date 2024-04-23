<?php

/**
 * Save 'Aggregated rating' value in Post meta
 */
function bob_update_aggregate_rating_on_post_edit($post_id, $post) {
    // Verify if post saved/created is type "post"
    if ($post->post_type == 'post') {

        $aggregated_rating = bob_generate_aggregated_rating_value($post_id);

        update_field('place_info_booking_place_aggregated_rating', $aggregated_rating, $post_id);
    }
}

add_action('save_post', 'bob_update_aggregate_rating_on_post_edit', 99, 2);


/**
 * Generate 'Aggregated rating' value and save in Post meta
 */
function bob_generate_aggregated_rating_value($post_id) {

    $best_of_balie_rating = calculate_best_of_bali_rating($post_id);

    $rating_info = bob_collect_post_rating_info($post_id);

    $aggregated_rating = bob_calculate_aggregated_rating_value($rating_info, $best_of_balie_rating);

    return $aggregated_rating;
}


/**
 * Collect all necessary rating info from Post to create 'Aggregated Rating value'
 */
function bob_collect_post_rating_info($post_id) {

    // Generate info in array
    $rating_info = array(
        "google" => array(
            "rating" => get_field('place_info_booking_place_google_rating', $post_id),
            "count" =>  get_field('place_info_booking_place_google_rating_totals', $post_id),
            "max_rate" => 5,
            "weight" => 1,
        ),
        "trip_advisor" => array(
            "rating" => get_field('place_info_booking_place_trip_advisor_rating', $post_id),
            "count" =>  get_field('place_info_booking_place_trip_advisor_rating_totals', $post_id),
            "max_rate" => 5,
            "weight" => 1,
        ),
        // "best_of_bali" => array(
        //     "rating" => get_field('best_of_bali_rating', $post_id),
        //     "count" =>  '1',
        //     "max_rate" => 5,
        //     "weight" => 1,
        // ),
    );
    // Add more rating providers if is necessary

    return bob_format_post_rating_info($rating_info);
}




/* 
calculate_best_of_bali_rating
 */
function calculate_best_of_bali_rating($post_id) {
    $best_of_bali_rating = get_field('best_of_bali_rating', $post_id);
    $best_of_bali_rating = !empty($best_of_bali_rating) ?  $best_of_bali_rating : 0;

    $rating_info = bob_collect_post_rating_info($post_id);

    $count_reviews = 0;

    foreach ($rating_info as $provider) {
        $count_reviews = $count_reviews + ($provider["count"]);
    }

    if (empty($count_reviews)) {
        $new_rating = $best_of_bali_rating;
    } else {
        $new_rating = $best_of_bali_rating * $count_reviews;
    }

    return $new_rating;
}



/**
 * Format and sanitize 'rating_info' from post
 */
function bob_format_post_rating_info($rating_info) {

    $formatted_rating_info = $rating_info;

    // Iterate over the array and apply format
    foreach ($formatted_rating_info as &$platform) {

        // Transform 'rating' to a float with a comma
        if (is_string($platform["rating"])) {
            $platform["rating"] = floatval(str_replace(",", ".", $platform["rating"]));
        }

        // Check if 'rating' is an empty string or "false" and convert it to 0
        if ($platform["rating"] === "" || $platform["rating"] === "false") {
            $platform["rating"] = 0;
        }

        // Transform 'count' to a float with a comma
        if (is_string($platform["count"])) {
            $platform["count"] = floatval(str_replace(",", ".", $platform["count"]));
        }

        // Check if 'count' is an empty string or "false" and convert it to 0
        if ($platform["count"] === "" || $platform["count"] === "false") {
            $platform["count"] = 0;
        }

        // Transform 'max_rate' to a float with a comma
        $platform["max_rate"] = floatval(str_replace(",", ".", $platform["max_rate"]));
    }

    return $formatted_rating_info;
}


/**
 * Calculate 'Aggregated rating' value from rating info array
 */
function bob_calculate_aggregated_rating_value($formatted_rating_info, $best_of_balie_rating) {

    $dividend = 0;
    $divisor = 0;

    $aggregated_result = 0;

    // Normalize ratings to 10 of 'max_rate' ratio 
    foreach ($formatted_rating_info as &$provider) {
        if (intval($provider["max_rate"]) === 5) {
            $provider["rating"] = $provider["rating"] * 2;
        }
    }

    // Generate elements of division based on rating formula (Ticket: 0108983)
    foreach ($formatted_rating_info as &$provider) {
        $dividend = $dividend + ($provider["rating"] * $provider["count"]);
        $divisor = $divisor + ($provider["count"]);
    }

    if (!empty($best_of_balie_rating)) {
        $dividend += $best_of_balie_rating;

        if (empty($divisor)) {
            $divisor += 1;
        } else {
            $divisor += $divisor;
        }
    }

    // Return 0 if all values are empty, to prevent divide by 0 and get error
    if ($dividend == 0 && $divisor == 0) {
        return 0;
    }

    // Make division to get aggregated rating value
    $aggregated_result = $dividend / $divisor;

    // Format result to reduce in 1 decimal and convert to string
    $formatted_aggregated_result = strval(number_format($aggregated_result, 1));

    return $formatted_aggregated_result;
}
