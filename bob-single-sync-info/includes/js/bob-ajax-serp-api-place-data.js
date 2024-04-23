(function ($) {
    $(document).ready(function () {

        // place_serp_api_sync_button
        if (document.querySelector("div[data-name='place_serp_api_sync_button")) {
            let serpApiButton = $(document).find("div[data-name='place_serp_api_sync_button']").find(".acf-button-group");
            serpApiButton.after('<div class="spinner spinner__serp-api"></div>');
            let serpApiSpinner = $(document).find('.spinner__serp-api');

            let button = document.querySelector("div[data-name='place_serp_api_sync_button")
            button = button.querySelector('.acf-button-group')

            button.addEventListener('click', function (e) {
                e.preventDefault();

                let place_name = document.querySelector('.acf-field[data-name="place_name"] input').value
                let latitude = document.querySelector('.acf-field[data-name="latitude"] input').value
                let longitude = document.querySelector('.acf-field[data-name="longitude"] input').value

                let data_req = {
                    action: 'bob_get_serp_api_place_data',
                    place_name: place_name,
                    latitude: latitude,
                    longitude: longitude,
                }

                serpApiSpinner.show();
                button.setAttribute('disabled', 'disabled')

                let xhr = new XMLHttpRequest();
                xhr.open('POST', '/wp-admin/admin-ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                xhr.onload = function (data) {
                    serpApiSpinner.hide();

                    if (xhr.status >= 200 && xhr.status < 400) {
                        button.removeAttribute('disabled')

                        let data = JSON.parse(xhr.responseText);

                        console.log(data)

                        let serpapi_price_range = document.querySelector('.acf-field[data-name="serpapi_price_range"] input')
                        let serp_api_hotel_class = document.querySelector('.acf-field[data-name="serp_api_hotel_class"] input')

                        serpapi_price_range.value = (data.rate_per_night && data.rate_per_night.extracted_lowest) ? data.rate_per_night.extracted_lowest : '';
                        serp_api_hotel_class.value = data.extracted_hotel_class ? data.extracted_hotel_class : '';
                    }
                };

                data_req = new URLSearchParams(data_req).toString();
                xhr.send(data_req);
            })
        }


    })
})(jQuery);
