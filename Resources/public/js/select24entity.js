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
      }
    };

    // Extend the parameters array with the one in arguments
    $.extend(select24entityParam, action);

    // Activate the select2 field
    this.select2(select24entityParam);

    // Return current field
    return this;
  };
});
