define(['jquery'], function($) {
    return {
        init: function(counterParam) {
            // Get the current URL.
            let url = new URL(window.location.href);

            // Check if the "counter" parameter exists.
            let counter = url.searchParams.get(counterParam);

            // If it exists, increment it; otherwise, set it to 1.
            counter = counter ? parseInt(counter) + 1 : 1;

            // Update the "counter" parameter in the URL.
            url.searchParams.set(counterParam, counter);

            // Reload the page with the updated URL after 60 seconds.
            setTimeout(function() {
                window.location.href = url.toString();
            }, 60000); // 60000 milliseconds = 60 seconds
        }
    };
});