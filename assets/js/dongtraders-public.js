/* js for copy paste qr generated url */
function dong_traders_url_copy(element) {
  var $temp = jQuery("<input>");
  jQuery("body").append($temp);
  $temp.val(jQuery(element).text()).select();
  document.execCommand("copy");
  $temp.remove();
}

(function ($) {
  $(function () {
    //initialize tab on backend
    $(document).on("click", ".update_card", function (ev) {
      $(".qr-tiger-vcard-code-generator").css("display", "block");
    }); // END OF DOCUMENT READY
  });
})(jQuery);
