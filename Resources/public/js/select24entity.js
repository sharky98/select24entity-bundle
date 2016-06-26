$(document).ready(function () {
  $.fn.select24entity = function (options) {
    var $s24 = $(this), cache = [];

    var baseParam = {
      ajax: {
        transport: function (params, success, failure) {
          // is caching enabled?
          if ($s24.data('ajax--cache')) {
            var key = params.data.q, cacheTimeout = $s24.data('ajax--cacheTimeout');
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
    };

    // Extend with other options
    $.extend(baseParam, options || {});

    // Responsive by default
    $s24.attr('style', 'width: 100%');

    // Activate the select2 field
    $s24.select2(baseParam);

    // If we have tag support activated, add a listener to add "new_" in front of new entries.
    if (baseParam.tags === true) {
      $s24.on('select2:selecting', function (e) {
        if (e.params.args.data.id === e.params.args.data.text) {
          e.params.args.data.id = 'new_' + e.params.args.data.id;
        }
      });
    }

    // Return current field
    return this;
  };
});
