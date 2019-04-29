(function($){
  /*
   * formatMoney
   *
   * This function is designed to format values
   * into dollar designated formats.
  */
  $.fn.formatMoney = function(){
    return this.val('$' + this.val().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,"));
  };

  $.fn.formatMoneyHtml = function(){
    var value = this.html();
    value = parseFloat(value).toFixed(2);

    return this.html('$' + value.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
  };

  /*
   * formatPhone
   *
   * This function is designed format values of
   * input to a phone number designed format.
  */
  $.fn.formatPhone = function(){
    // Get the value.
    var value = this.val();

    value = "(" + value.substr(0,3) + ") " + value.substr(3,3) + "-" + value.substr(6,4);

    return this.val(value);
  };

  /*
   * formatPercent
   *
   * This function is designed format values of
   * input to a percent designed format, to a scale 
  */
  $.fn.formatPercent = function(scale=2){
    var val = (this.val() * 100).toFixed(scale);
    return this.html(val + '%');
  };

  $.fn.formatPercentHtml = function(scale=2){
    var val = (this.html() * 100).toFixed(scale);
    return this.html(val + '%');
  };

  /*
   * stripToNumeric
   *
   * This function is designed to remove previous symbols
   * formatting on the value to a numeric.
  */
  $.fn.stripToNumeric = function(){
    return this.val(this.val().replace(/\D/g,''));
  };
}(jQuery));
