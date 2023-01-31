jQuery(document).ready(function($){
  /*Retreives Data From session storage */
  let data = sessionStorage.getItem("lastid");
  /* Checks The Last Tab clicked from session storage and makes the same tab active from session storage */
  if (data) $("#" + data).addClass("ui-tabs-active ui-state-active");
  /*When any tab is clicked from settings sections the tab id is stored in session storage */
  $("#dongtrader-tabs>li").on("click", function () {
    sessionStorage.setItem("lastid", $(this).prop("id"));
  });
  /*Saves Data On Options via ajax for the from inside Integration API Tab from settings section*/
  $("#save-settings").submit(function () {
    var b = $(this).serialize();
    $(".save-settings-dash").val("Saving...");
    $.post("options.php", b)
      .error(function () {
        var html = `<div class="error-box"></div>`;
        $(html).insertAfter(".settings-submit");
        setTimeout(function () {
          $(".error-box").remove();
        }, 1000);
        $(".save-settings-dash").val("Confirm Changes");
      })
      .success(function () {
        var html = `<div class="success-box">Settings Saved Successfully</div>`;
        $(html).insertAfter(".settings-submit");
        setTimeout(function () {
          $(".success-box").remove();
        }, 1000);
        $(".save-settings-dash").val("Confirm Changes");
      });
    return false;
  });

  /* A jQuery function to initialize the tabs. */
  $(function () {
    $("#tabs-wrap").tabs();
  });

  function animate_button(show) {
    if (show) {
      $(".real-button").css("display", "none");
      $(".anim-button").css("display", "");
    } else {
      $(".real-button").css("display", "");
      $(".anim-button").css("display", "none");
    }
  }
  /* When The Form is submitted from Qr Code tabs in settings sections */
  $(document).on("submit", ".qrtiger-form", function (ev) {
    ev.preventDefault();

    animate_button(true);
    // $('.custom-load').css('display', '');
    $(".dong-notify-msg").empty().fadeIn("fast");
    $(".form-loader").css("display", "block");
    var qRsize = $(".qrtiger-size").val(),
      qRurl = $(".qrtiger-url").val(),
      qRcolor = $(".qrtiger-color").val();
    var datas = {
      action: "dongtrader_generate_qr2",
      type: "JSON",
      qrsize: qRsize,
      qrcolor: qRcolor,
      qrurl: qRurl,
    };

    dong_ajax_request(datas);
  });

  /**
   * It takes the data from the form and sends it to the server using ajax
   * @param data - The data to be sent to the server.
   */
  function dong_ajax_request(data) {
    $.post(dongScript.ajaxUrl, data, function (rdata) {
      /*  Parse jason data to object */
      var resp = JSON.parse(rdata);
      /*  Set icon class either error or succes*/
      var iconClass = resp.dataStatus ? `fa fa-check` : `fa fa-times-circle`;
      /*  Setting the value of the variable `msgClass` to `success-msg` if the value of
        `resp.dataStatus` is true, and `error-msg` if the value of `resp.dataStatus` is false. */
      var msgClass = resp.dataStatus ? `success-msg` : `error-msg`;
      /*  Setting the value of the variable `msgText` to `QR code generated successfully` if the value of
        `resp.dataStatus` is true, and `All fields are required` if the value of `resp.dataStatus` is false. */
      var msgText = resp.dataStatus
        ? `QR code generated successfully.`
        : `All fields are required`;
      /*  Response Html Message Combined to display the response status as error or valid*/
      var responseHtml = `<div class="${msgClass}"><i class="${iconClass}"></i>${msgText}</div>`;
      /*  if api response is ok and ajax response data is valid */
      if (resp.dataStatus && resp.apistatus) {
        $(".dong-notify-msg").append(responseHtml).fadeOut(2000, "swing");
        $("#openModal1").fadeOut(2500, "swing");
        animate_button(false);
        sessionStorage.setItem("lastid", "second");
        window.location.reload();
      } else if (resp.dataStatus && !resp.apistatus) {
        /*  if api response is bad and ajax response data is valid */
        var notifyHtml = `<div class="error-msg"><i class="fa fa-times-circle"></i>Api Error! Please Try Again</div>`;
        $(".dong-notify-msg").append(notifyHtml).fadeOut(2000, "swing");
        animate_button(false);
      } else {
        /*  if everything gone wrong */
        $(".dong-notify-msg").append(responseHtml).fadeOut(2000, "swing");
        animate_button(false);
      }
      animate_button(false);
    });
  }

  $(document).on("click", ".url-copy", function (cp) {
    cp.preventDefault();
    var urlcp = $(this).attr("data-url");
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(urlcp).select();
    if (document.execCommand("copy") && $temp.remove())
      alert("QR URL copied to clipboard");
  }); 

  $(document).on("click", ".qr-remover", function (rm) {
    rm.preventDefault();
    var itemId = $(this).attr('data-remove');
    var metaKey = $(this).attr('data-meta');
    console.log(metaKey);
    var cloned = $(this).parent().clone();
    var container = $(this).parent();
    var save = $('.save-variation-changes');
    var loop = $(this).attr('data-index');
    var button = `<button data-index="${loop}" data-variable="true" data-initiator="_product-qr-variabled" data-id="${itemId}" class=" button button-primary button-large generate-variable-qr">Generate Product QR</button><input data-id="${itemId}" type="hidden" name="variable_product_qr_data" id="variable_product_qr_data[${loop}]" value="">`
    $('#variable_description' + loop).trigger('change');
    $.post(dongScript.ajaxUrl, { action : 'dongtrader_delete_qr_fields', itemID : itemId , metakey:metaKey }, function (mData) {
      container.empty();
      save.trigger('click');
    });
     
  }); 



  function qr_generator(button) {
    button.on('click', function (e) {
      e.preventDefault();
      button.text('Generating...')
      var postId = $(this).attr("data-productid"),
        evtAction = $(this).attr("data-initiator");
        inPut = $(this).next("input"),
        mainContainer =$(this).parent(".dong-qr-components"),
        variations = $(this).attr(".data-variable"); 
      
        initiate_ajax_request(
          {

            action: "dongtrader_meta_qr_generator",
            productnums: postId,
            variations: variations,
            intiator: evtAction,
          },
          inPut,
          mainContainer,
          $(this)
        );
      
      
    });
  }


  function initiate_ajax_request(datas, inPut, mainContainer, button) {
    $.post(dongScript.ajaxUrl, datas, function (mData) {
      console.log(mData);
      var jsonData = JSON.parse(mData);
      if (jsonData.success) {
        mainContainer.empty();
        mainContainer.append(jsonData.template);
        inPut.val(mData);
        button.text('Generate Product QR');
      }
      //button.text("Generate Product QR");
    });
  }
  qr_generator($(".generate-product-qr"));
  qr_generator($(".generate-product-qr-direct-checkout"));
  //Scenario changed a bit for meta fields

    $('#woocommerce-product-data').on('woocommerce_variations_loaded', function (event) {
        $('.generate-variable-qr').on('click', function (e) {
              e.preventDefault();
              var loop = $(this).attr('data-index');
              var postId = $(this).attr("data-productid"),
              evtAction = $(this).attr("data-initiator"),
              inPut = $(this).next("input"),
              mainContainer =$('#dong-qr-components'+loop),
                variations = $(this).attr("data-id");
              $('#variable_description' + loop).trigger('change');
              initiate_ajax_request(
                {
                  action: "dongtrader_meta_qr_generator",
                  productnums: postId,
                  variations: variations,
                  intiator: evtAction,
                  loop : loop,
                },
                inPut,
                mainContainer,
                $(this)
              );
         // window.location.reload();
        });

    });
});
