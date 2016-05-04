$(document).ready(function () {
  $.fn.select24entity = function (action) {
    // Create the parameters array with basic values
    var select24entityParam = {
      ajax: {
        data: function (params) {
          return {
            q: params.term
          };
        },
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      },
      tags: false
    };

    // Extend the parameters array with the one in arguments
    $.extend(select24entityParam, action);

    // Responsive by default
    this.attr('style', 'width: 100%');

    // Activate the select2 field
    this.select2(select24entityParam);

    // If we have tag support activated, add a listener to add "new_" in front of new entries.
    if (select24entityParam.tags === true) {
      this.on('select2:selecting', function (e) {
        if (e.params.args.data.id === e.params.args.data.text) {
          e.params.args.data.id = 'new_' + e.params.args.data.id;
        }
      });
    }

    // Return current field
    return this;
  };
});
