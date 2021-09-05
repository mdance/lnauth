(function ($, Drupal) {
  'use strict';

  var ns = 'lnauth';

  Drupal.behaviors[ns] = {
    attach: function (context, settings) {
      var $window, subsettings, instance, k1, frequency, attempts, url;

      $window = $(window);

      if (settings[ns]) {
        subsettings = settings[ns];

        for (var delta in subsettings) {
          instance = subsettings[delta];

          k1 = instance['k1'];
          frequency = instance['frequency'];
          attempts = instance['attempts'];
          url = instance['url'];

          $window.once(k1).each(
            function() {
              var interval, attempt;

              attempt = 0;

              interval = setInterval(
                function() {
                  if (attempts == 0 || attempt <= attempts) {
                    $.get(
                      url,
                      {},
                      function (response, status) {
                        if (response && response.authenticated === true) {
                          clearInterval(interval);
                          location.reload();
                        }
                      }
                    );
                  }

                  attempt++;
                },
                frequency
              );

              if (attempts != 0 && attempt >= attempts) {
                clearInterval(interval);
              }
            }
          );
        }
      }
    }
  }
})(jQuery, Drupal, drupalSettings);
