


document.addEventListener("DOMContentLoaded", function () {
    var buttons = document.querySelectorAll('.change-rating-btn');

    buttons.forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            var postID = this.getAttribute('data-post-id');
            var newRating = prompt('Enter new rating:');

            if (newRating !== null) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', ajaxurl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                xhr.onload = function (data) {
                    //console.log(data)
                    if (xhr.status === 200) {
                        location.reload();
                    } else {
                        console.error(xhr.responseText);
                    }
                };
                xhr.onerror = function () {
                    console.error(xhr.statusText);
                };
                xhr.send('action=change_post_rating&post_id=' + encodeURIComponent(postID) + '&new_rating=' + encodeURIComponent(newRating));
            }
        });
    });
});

