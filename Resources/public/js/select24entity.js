$(document).ready(function () {
  $.fn.select24entity = function (action) {
    if (action) {
      this.select2(action);
      return this;
    }
    this.select2({
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
    });
    return this;
  };

  $('.select24entity').select24entity();
});
