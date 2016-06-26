$(document).ready(function () {
  $.fn.select24entity = function (options) {
    this.each(function () {
      // Keep a reference to the element so we can keep the cache local to this instance and so we can
      // determine if caching is even enabled on this element by looking at it's data attributes since select2
      // doesn't expose its options to the transport method.
      var $s2 = $(this), cache = [];
      $s2.select2($.extend({
        ajax: {
          transport: function (params, success, failure) {
            // is caching enabled?
            if ($s2.data('ajax--cache')) {
              var key = params.data.q, cacheTimeout = $s2.data('ajax--cacheTimeout');
              // no cache entry for 'term' or the cache has timed out?
              if (typeof cache[key] == 'undefined' || (cacheTimeout && Date.now() >= cache[key].time)) {
                $.ajax(params).fail(failure).done(function (data) {
                  cache[key] = {
                    data: data,
                    time: cacheTimeout ? Date.now() + cacheTimeout : null
                  };
                  success(data);
                });
              } else {
                // return cached data with no ajax request
                success(cache[key].data);
              }
            } else {
              // no caching enabled. just do the ajax request
              $.ajax(params).fail(failure).done(success);
            }
          },
          data: function (params) {
            return {
              q: params.term
            };
          },
          processResults: function (data) {
            return {
              results: data
            };
          }
        },
        tags: false
      }, options || {}));

      // Responsive by default
      this.attr('style', 'width: 100%');

      // Activate the select2 field
      this.select2($s2.select2);

      // If we have tag support activated, add a listener to add "new_" in front of new entries.
      if ($s2.select2.tags === true) {
        this.on('select2:selecting', function (e) {
          if (e.params.args.data.id === e.params.args.data.text) {
            e.params.args.data.id = 'new_' + e.params.args.data.id;
          }
        });
      }
    });

    // Return current field
    return this;
  };
});
