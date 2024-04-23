(function ($) {
    $(document).ready(function () {

        // Google sync button and ID input
        let googleSyncButton = $(document).find("div[data-name='place_google_sync_button']").find(".acf-button-group");
        let googlePlaceIdInput = $(document).find("div[data-name='place_google_id']").find('input');

        // Generate loading spinner html
        googleSyncButton.after('<div class="spinner spinner__google"></div>');
        let googleSpinner = $(document).find('.spinner__google');

        // 'Place' inputs to populate
        let inputName = $(document).find('.acf-field[data-name="place_name"] input');
        let inputAddress = $(document).find('.acf-field[data-name="place_address"] input');
        let inputAddressLink = $(document).find('.acf-field[data-name="place_address_link"] input');
        //  let inputOpeningHours = $(document).find('.acf-field[data-name="place_opening_hours"] input');
        let inputGoogleRating = $(document).find('.acf-field[data-name="place_google_rating"] input');
        let inputGoogleRatingTotals = $(document).find('.acf-field[data-name="place_google_rating_totals"] input');
        let price_range = $(document).find('.acf-field[data-name="place_price_range"] input');

        let latitude = $(document).find('.acf-field[data-name="latitude"] input');
        let longitude = $(document).find('.acf-field[data-name="longitude"] input');

        /**
         * Populate all Google Maps input values in single place post
         */
        function bob_single_place_populate_google_fields(data) {
            inputName.val(data.name ? data.name : "");
            inputAddress.val(data.adr_address ? data.adr_address : "");
            inputAddressLink.val(data.url ? data.url : "");
            // inputOpeningHours.val(data.opening_hours && data.opening_hours.weekday_text ? data.opening_hours.weekday_text.join(', ') : "");
            inputGoogleRating.val(data.rating ? data.rating : "");
            inputGoogleRatingTotals.val(data.user_ratings_total ? data.user_ratings_total : "");

            latitude.val(data.geometry.location.lat ? data.geometry.location.lat : "");
            longitude.val(data.geometry.location.lng ? data.geometry.location.lng : "");

            if (price_range.val() == '') {
                price_range.val(data.price_level ? data.price_level : "");
            }
        }

        // Event logic for "Sync info" button click        
        googleSyncButton.on('click', function (event) {
            event.preventDefault();

            // Get current 'Place ID' input value
            let googlePlaceIdInputCurrentValue = googlePlaceIdInput.val();

            if (googlePlaceIdInputCurrentValue) {

                googleSpinner.show();

                // Ajax call
                $.ajax({
                    url: '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    cache: false,
                    data: {
                        action: 'bob_get_google_maps_place_data',
                        place_google_id: googlePlaceIdInputCurrentValue
                    },
                    success: function (response) {

                        console.log(response)

                        if (!response) {
                            alert('Connection error');
                            googleSpinner.hide();
                            return;
                        }

                        // If request is OK but empty
                        if (response.error_message) {
                            alert(response.error_message);
                            googleSpinner.hide();
                            return;
                        }

                        let data = response.result;

                        // Populate fields
                        bob_single_place_populate_google_fields(data);

                        googleSpinner.hide();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(textStatus + ': ' + errorThrown);
                        googleSpinner.hide();
                    }
                });
            } else {
                alert('No value was entered in the "Google Maps Place ID" field.')
            }
        })
    })
})(jQuery);
