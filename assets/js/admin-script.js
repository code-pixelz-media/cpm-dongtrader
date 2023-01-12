(function ($) {

/*Retreives Data From session storage */
    let data = sessionStorage.getItem('lastid');
/* Checks The Last Tab clicked from session storage and makes the same tab active from session storage */
    if (data) $('#' + data).addClass('ui-tabs-active ui-state-active');
/*When any tab is clicked from settings sections the tab id is stored in session storage */
    $('#dongtrader-tabs>li').on('click', function () {
        sessionStorage.setItem('lastid', $(this).prop('id'));
    });
/*Saves Data On Options via ajax for the from inside Integration API Tab from settings section*/
    $('#save-settings').submit( function () {
        var b = $(this).serialize();
        $('.save-settings-dash').val('Saving...');
        $.post( 'options.php', b )
        .error( function() {
            var html = `<div class="error-box"></div>`;
            $(html).insertAfter('.settings-submit');
            setTimeout(function(){ $('.error-box').remove(); }, 1000);
            $('.save-settings-dash').val('Confirm Changes');
        })
        .success( function() {
            var html = `<div class="success-box">Settings Saved Successfully</div>`;
            $(html).insertAfter('.settings-submit');
            setTimeout(function(){ $('.success-box').remove(); }, 1000);
            $('.save-settings-dash').val('Confirm Changes');
        });
        return false;    
    });

/* A jQuery function to initialize the tabs. */
    $(function () {
        $('#tabs-wrap').tabs();
    }); 

/* When The Form is submitted from Qr Code tabs in settings sections */
    $(document).on("submit", ".qrtiger-form", function (ev) {
        ev.preventDefault();
        
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
            ? `QR code generated successfully.Please Check In Qr Lists Tab`
            : `All fields are required`;
        /*  Response Html Message Combined to display the response status as error or valid*/
            var responseHtml = `<div class="${msgClass}"><i class="${iconClass}"></i>${msgText}</div>`;
        /*  if api response is ok and ajax response data is valid */
            if (resp.dataStatus && resp.apistatus) {
                $(".dong-notify-msg").append(responseHtml).fadeOut(2000, "swing");
                $("#openModal1").fadeOut(2500, "swing");
                window.location.reload();
            }
        /*  if api response is bad and ajax response data is valid */
            else if (resp.dataStatus && !resp.apistatus)
            {
                var notifyHtml = `<div class="error-msg"><i class="fa fa-times-circle"></i>Api Error! Please Try Again</div>`;
                $(".dong-notify-msg").append(notifyHtml).fadeOut(2000, "swing");
            }
        /*  if everything gone wrong */
            else
            {
                $(".dong-notify-msg").append(responseHtml).fadeOut(2000, "swing");
            }
        $(".form-loader").css("display", "none");
    });
  }
    
    $('.qr-pic .delete').on('click', function (d) {
        d.preventDefault();
        var el = $(this).closest('div.qr-pic');
        var qr_id = $(this).attr('data-qrid'), qr_index = $(this).attr('data-index');
        
        $.post(dongScript.ajaxUrl, { action: "dongtrader_delete_qr", type: "JSON",qrID : qr_id , qrIndex:qr_index , }, function (resp) {
            var obj = JSON.parse(resp);
            if (obj.success) {
                el.remove();
                
            }
        });
       
    })
    $('.qr-pic .copy').on('click', function (d) {
        d.preventDefault();
        var linkCopy = $(this).attr('data-url');
        if (navigator.clipboard.writeText(linkCopy)) {
            alert('Qr Code Link Copied To Clipboard');
        }   
    })

})(jQuery);

