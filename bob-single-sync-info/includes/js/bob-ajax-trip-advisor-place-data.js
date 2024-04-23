(function ($) {
    $(document).ready(function () {
        // Trip Advisor sync button and ID input
        let tripAdvisorSyncButton = $(document).find("div[data-name='place_trip_advisor_sync_button']").find(".acf-button-group");
        let tripAdvisorPlaceIdInput = $(document).find("div[data-name='place_trip_advisor_id']").find('input');

        // Generate loading spinner html
        tripAdvisorSyncButton.after('<div class="spinner spinner__trip-advisor"></div>');
        let tripAdvisorSpinner = $(document).find('.spinner__trip-advisor');

        // 'Place' inputs to populate
        let inputTripAdvisorRating = $(document).find('.acf-field[data-name="place_trip_advisor_rating"] input');
        let inputTripAdvisorRatingTotals = $(document).find('.acf-field[data-name="place_trip_advisor_rating_totals"] input');
        let inputBookNowButtonLink = $(document).find('.acf-field[data-name="place_book_now_link"] input');
        let price_range = $(document).find('.acf-field[data-name="place_price_range"] input');
      

        /**
         * Populate all Google Maps input values in single place post
         */
        function bob_single_place_populate_trip_advisor_fields(data) {
            inputTripAdvisorRating.val(data.rating ? data.rating : "");
            inputTripAdvisorRatingTotals.val(data.num_reviews ? data.num_reviews : "");

            if (price_range.val() == '') {
                price_range.val(data.price_level ? data.price_level : "");
            }
        }

        // Event logic for "Sync info" button click        
        tripAdvisorSyncButton.on('click', function (event) {
            event.preventDefault();

            // Get current 'Trip Advisor Place ID' input value
            let tripAdvisorIdInputCurrentValue = tripAdvisorPlaceIdInput.val();

            if (tripAdvisorIdInputCurrentValue) {

                tripAdvisorSpinner.show();

                // Ajax call
                $.ajax({
                    url: '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    cache: false,
                    data: {
                        action: 'bob_get_trip_advisor_place_data',
                        place_trip_advisor_id: tripAdvisorIdInputCurrentValue
                    },
                    success: function (response) {
                      console.log(response)

                        if (!response) {
                            alert('Connection error');
                            tripAdvisorSpinner.hide();
                            return;
                        }

                        // If request is OK but empty
                        if (response.error) {
                            alert(response.error.message);
                            tripAdvisorSpinner.hide();
                            return;
                        }

                        let data = response;

                        // Populate fields
                        bob_single_place_populate_trip_advisor_fields(data);

                        tripAdvisorSpinner.hide();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(textStatus + ': ' + errorThrown);
                        tripAdvisorSpinner.hide();
                    }
                });
            } else {
                alert('No value was entered in the "Trip Advisor Place ID" field.')
            }
        })
    })
})(jQuery);
