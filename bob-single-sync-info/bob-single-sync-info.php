<?php
/**
* Plugin Name: Sync single place data
* Plugin URI: https://www.serfe.com/en/
* Description: Implementation to synchronize single post place information.
* Version: 1.0
* Author: Serfe
* Author URI: https://www.serfe.com/en/
**/

defined('ABSPATH') || exit;

if (!defined('BOB_SINGLE_SYNC_DATA_FILE')) {
    define('BOB_SINGLE_SYNC_DATA_FILE', __FILE__);
}

include_once dirname(BOB_SINGLE_SYNC_DATA_FILE) . '/includes/bob-get-google-maps-place-data.php';
include_once dirname(BOB_SINGLE_SYNC_DATA_FILE) . '/includes/bob-get-trip-advisor-place-data.php';
include_once dirname(BOB_SINGLE_SYNC_DATA_FILE) . '/includes/bob-get-serp-api-place-data.php';
include_once dirname(BOB_SINGLE_SYNC_DATA_FILE) . '/includes/bob-generate-aggregated-rating.php';
include_once dirname(BOB_SINGLE_SYNC_DATA_FILE) . '/includes/bob-auto-update-rating-places.php';
