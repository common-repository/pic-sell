const {__, _n, _x, sprintf} = wp.i18n;

function add_field_row() {
  var contents = jQuery("#master-row").html();
  contents = contents.replaceAll("modele_tr", "tr");
  contents = contents.replaceAll("modele_td", "td");
  var tbody = jQuery("#field_wrap tbody");
  var table = tbody.length ? tbody : jQuery("#field_wrap");
  table.append(contents);
  ps_init_gallery();
  ps_init_classement();
}
function add_field_email(){
    var modele = '<input type="text" name="espaceprive_email_client[]" value="" />';
    var wrap = jQuery("#wrap-emails-clients");
    wrap.append(modele);
}

function ps_init_classement() {
  var tr = jQuery("#field_wrap tr:not(.tr_head)");
  if (tr.length) {
    jQuery("#field_wrap tbody tr:not(.tr_head)").each(function () {
      jQuery(this).find(".ps_classement_span").html(jQuery(this).index());
      jQuery(this).find(".ps_classement_input").val(jQuery(this).index());
      
    });
  }
}

function ps_init_gallery() {
  var tr = jQuery("#field_wrap tr:not(.tr_head)");
  if (tr.length) {
    jQuery("#field_wrap").show();
  } else {
    jQuery("#field_wrap").hide();
  }
}

const toBase64 = (file) =>
  new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = (error) => reject(error);
  });

  function toBase64_simple(file){

   return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = () => resolve(reader.result);
      reader.onerror = (error) => reject(error);
    });

  }

jQuery(function ($) {
  /**
   * TABLEAU SORTABLE DRAG
   */
  $("#field_wrap tbody").sortable({
    update: function (event, ui) {
      $("#field_wrap tbody tr").each(function () {
        $(this).find(".ps_classement_span").html($(this).index());
        $(this).find(".ps_classement_input").val($(this).index());
      });
    },
    handle: ".ps_classement",
    cursor: "move",
  });

  ps_init_gallery();
  ps_init_classement();

  var slice_size = 1024 * 1024 * 10;
  var post_id = PicSellVars.post.ID;
  var post_title = PicSellVars.post.post_title;
  var nonce_ajax = PicSellVars.nonce; 
  var individual_file = {};
  var $parent = {};
  var $ligne = {};
  var $file = {};

$("body").on("click", ".ps_add_multiple_media", function (e) { /**Simule input file */

    if(!$('input').hasClass('pic_multiple_media')){   
      var input = $('<input/>')
      .attr('type', "file")
      .attr('name', "pic_multiple_media")
      .attr('multiple', 'multiple')
      .attr('class', "pic_multiple_media")
      .attr('id', "pic_multiple_media").hide();
      $(e.target).parent().append(input);
    }
    $('input[name=pic_multiple_media').val('');
    $('input[name=pic_multiple_media').click();
    
});


$("body").on("change", ".pic_multiple_media", async function (e) {

  var files = $(this)[0].files;
  for (var i = 0, f; f = files[i]; i++) {
        var isImage = f.type.split("/")[0] === "image";
        var isVideo = f.type.split("/")[0] === "video";
        
        if(isImage || isVideo){

          if(isImage){

            var file_b64 = await toBase64_simple(f); 

            $.when(file_b64).done(function(){
          
                add_field_row();
                var $ligne = $("#field_wrap tbody tr:nth-child(" + $("#field_wrap tbody > tr").length + ")"); 

                fd = new FormData();       
                fd.append('nonce_ajax', nonce_ajax);
                fd.append("file", f);
                fd.append("post_title", post_title);
                fd.append("post_id", post_id);
                fd.append("action", "fiu_upload_file");
            
                var base = $.ajax({
                  async: false,
                  type: "POST",
                  url: ajaxurl,
                  data: fd,
                  contentType: false,
                  processData: false,
                  cache: false,
                });  
                base.done(function (result, textStatus, jqXHR) {
                  var responseText = jqXHR.responseText;
                  var responseTextLen = responseText.length;                    
                    response = JSON.parse(responseText);
                    $ligne.find(".ps_choice_image_select").val("image");
                    $ligne.find(".ps_display_image").attr("src", file_b64).show();
                    $ligne.find(".ps_remove_media_button").show();
                    $ligne.find(".ps_media_dir").val(response.bdir);
                    $ligne.find(".ps_media_title").val(f.name.split(".")[0]);
                });
            });
            
          } //isImage

          if(isVideo){

            add_field_row(); 
            individual_file = f;
            blob = {};
            var start = 0;

            var $ligne = $("#field_wrap tbody tr:nth-child(" + $("#field_wrap tbody > tr").length + ")");
            
            var base = await upload_file_multiple(f, start, $ligne)
            
            $.when(base).done(function () {
              $ligne.find(".ps_choice_image_select").val("video");
              $ligne.find(".ps_remove_media_button").show();
              $ligne.find(".ps_media_title").val(f.name.split(".")[0]);
            });

          }//isVideo

        } //isImage OR //isVideo        

  }

});

  $("body").on("change", ".ps_upload_image_button", async function (e) {
    e.preventDefault();

    var $parent = $(this).parent(),
      $ligne = $parent.parent(),
      $file = $(this),
      individual_file = $file[0].files[0],
      name_file = individual_file.name,
      fd = new FormData();
    var post_id = PicSellVars.post.ID;
    var post_title = PicSellVars.post.post_title;

    var file_b64 = await toBase64(individual_file);

    var isImage = individual_file.type.split("/")[0] === "image";
    if (!isImage) {
      alert("Seule les images sont autorisées");
      $file.val("");
      return false;
    }

    fd.append('nonce_ajax', nonce_ajax);
    fd.append("file", individual_file);
    fd.append("post_title", post_title);
    fd.append("post_id", post_id);
    fd.append("action", "fiu_upload_file");

    jQuery.ajax({
      type: "POST",
      url: ajaxurl,
      data: fd,
      contentType: false,
      processData: false,
      success: function (response) {
        response = JSON.parse(response);
        $ligne.find(".ps_display_image").attr("src", file_b64).show();
        $file.val("").hide();
        $ligne.find(".ps_remove_media_button").show();
        $ligne.find(".ps_media_dir").val(response.bdir);
        $ligne.find(".ps_media_title").val(name_file.split(".")[0]);
      },
    });
  });

  $("body").on("click", ".ps_remove_media_button", function () {
    $(this).parent().parent().find(".ps_display_image").attr("src", "").hide();
    $(this).parent().parent().find(".ps_media_url").val("");
    $(this).parent().parent().find(".ps_choice_image_select").trigger("change");
    $(this).hide();
    return false;
  });

  $("body").on("click", ".ps_remove_line_button", function () {
    $(this).parent().parent().remove();
    ps_init_gallery();
    return false;
  });

  $("body").on("change", ".ps_choice_image_select", function () {
    var val = $(this).val();

    if (val == "image") {
      $(this).parent().parent().find(".ps_upload_video_button").hide();

      if (!$(this).parent().parent().find(".ps_display_image").is(":visible")) {
        $(this).parent().parent().find(".ps_display_video").hide();
        if (
          $(this).parent().parent().find(".ps_display_image").attr("src") != ""
        ) {
          $(this).parent().parent().find(".ps_display_image").show();
          $(this).parent().parent().find(".ps_remove_media_button").show();
        } else {
          $(this).parent().parent().find(".ps_upload_image_button").show();
          $(this).parent().parent().find(".ps_remove_media_button").hide();
        }
      }
    } else if (val == "video") {
      $(this).parent().parent().find(".ps_upload_image_button").hide();

      if (!$(this).parent().parent().find(".ps_display_video").is(":visible")) {
        $(this).parent().parent().find(".ps_display_image").hide();

        if (
          $(this)
            .parent()
            .parent()
            .find(".ps_display_video")
            .find("source")
            .attr("src") != ""
        ) {
          $(this).parent().parent().find(".ps_display_video").show();
          $(this).parent().parent().find(".ps_remove_media_button").show();
        } else {
          $(this).parent().parent().find(".ps_upload_video_button").show();
          $(this).parent().parent().find(".ps_remove_media_button").hide();
        }
      }
    } else {
      $(this).parent().parent().find(".ps_upload_video_button").hide();
      $(this).parent().parent().find(".ps_upload_image_button").hide();
    }
    //	$(this).parent().parent().remove();
    return false;
  });

  $("body").on("change", ".ps_upload_video_button", async function (e) {
    e.preventDefault();

    $parent = $(this).parent();
    $ligne = $parent.parent();
    $file = $(this);
    individual_file = $file[0].files[0];
    blob = {};
    final_video = URL.createObjectURL(individual_file);

    var isVideo = individual_file.type.split("/")[0] === "video";
    if (!isVideo) {
      alert("Seule les vidéos sont autorisées");
      $file.val("");
      return false;
    }

    upload_file(0);
  });
  async function upload_file(start) {
    var next_slice = start + slice_size;
    blob = individual_file.slice(start, next_slice);

    var file_b64 = await toBase64(blob);

    fd2 = new FormData();
    fd2.append('nonce_ajax', nonce_ajax);
    fd2.append("filename", individual_file.name);
    fd2.append("post_title", post_title);
    fd2.append("post_id", post_id);
    fd2.append("action", "fiu_upload_file_video");
    fd2.append("video", file_b64);

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      contentType: false,
      processData: false,
      data: fd2,
      success: function (response) {
        response = JSON.parse(response);
        var size_done = start + slice_size;
        var percent_done = Math.floor((size_done / individual_file.size) * 100);

        if (next_slice < individual_file.size) {
          $ligne.find(".ps-upload-progress").show();
          $ligne
            .find(".ps-upload-progress .uploading")
            .css({ width: percent_done + "%" })
            .show()
            .find("span")
            .html(percent_done + "%");
          upload_file(next_slice);
        } else {
          $file.val("").hide();
          $ligne.find(".ps-upload-progress .uploading").hide();
          $ligne.find(".ps-upload-progress .finished").show();
          $ligne.find(".ps_remove_media_button").show();
          $ligne.find(".ps_media_dir").val(response.bdir);

          $ligne
            .find(".ps_display_video")
            .show()
            .find("source")
            .attr("src", final_video);
          $ligne.find(".ps_display_video").get(0).load();
        }
      },
    });
    // };
  }

  async function upload_ajax_file(file, start, $ligne){

    var slice_size = 1024 * 1024 * 10;
    var individual_file = file;
    var next_slice = start + slice_size;
    var blob = file.slice(start, next_slice);
    var ajax;
      
    var file_b64 = await toBase64_simple(blob);

    $.when(file_b64).done(function(){

      var fd2 = new FormData();
      fd2.append('nonce_ajax', nonce_ajax);
      fd2.append("filename", individual_file.name);
      fd2.append("post_title", post_title);
      fd2.append("post_id", post_id);
      fd2.append("action", "fiu_upload_file_video");
      fd2.append("video", file_b64);

      ajax = $.ajax({
        url: ajaxurl,
        type: "POST",
        contentType: false,
        processData: false,
        data: fd2,
        cache: false     
      });

      return ajax;
    });

    return ajax;

  }

 async function upload_file_multiple(file, start, $ligne) {

  //return new Promise(async (resolve, reject) => {
    $upload_ajax = await upload_ajax_file(file, start, $ligne);

    $.when($upload_ajax).done(function(result, textStatus, jqXHR){ 

      var slice_size = 1024 * 1024 * 10;
      var next_slice = start + slice_size;

      var size_done = start + slice_size;
      var percent_done = Math.floor((size_done / individual_file.size) * 100);
        if (next_slice < file.size) {
          $ligne.find(".ps-upload-progress").show();
          $ligne
            .find(".ps-upload-progress .uploading")
            .css({ width: percent_done + "%" })
            .show()
            .find("span")
            .html(percent_done + "%");
            return upload_file_multiple(file, size_done, $ligne);
        } else { 
          var final_video = URL.createObjectURL(file);         
          var responseText = result; 
          response = JSON.parse(responseText);

          $ligne.find(".ps-upload-progress .uploading").hide();
          $ligne.find(".ps-upload-progress .finished").show();
          $ligne.find(".ps_remove_media_button").show();
          $ligne.find(".ps_media_dir").val(response.bdir);

          $ligne
            .find(".ps_display_video")
            .show()
            .find("source")
            .attr("src", final_video);
          $ligne.find(".ps_display_video").get(0).load();
         // resolve(true);
         // return true;
        }        
    });

    return $upload_ajax;


        
     // });


    // };
  }


   $('body').on("change", '#espaceprive_date_left', function(){
        $('#espaceprive_date_left_rec').val($('#espaceprive_date_left').val());
    });

  /**SAVE POST INTERCEPT FOR SEND EMAIL WITH TIMER */
  $("#publish").click(async function (e) {
    e.preventDefault();

    $('body, html').css({'overflow':'hidden', 'max-height':'100vh'});
    $('body').append('<div id="pic_overlay"></div>');
    $('body').append('<div id="modal_before_publish" title="'+__('Confirm publish', 'pic_sell_plugin')+'"></div>');

    fd2 = new FormData();
    fd2.append('nonce_ajax', nonce_ajax);
    fd2.append("action", "pic_template_sent_gallery");
    fd2.append('post_id', post_id);
    fd2.append('act', 'step_1');
    var ajax = await $.ajax({
      url: ajaxurl,
      type: "POST",
      contentType: false,
      processData: false,
      data: fd2,
    });
    $("#modal_before_publish").html(ajax);

    var mailsend = $(".ps_send_gallery").hasClass("resend")?true:false;

    $("#modal_before_publish").on("click", ".ps_send_gallery", async function(e){

    $("#modal_before_publish").html("<div style='display: flex;align-items: center;justify-content: center;font-size: 70px;'><i class='fas fa-circle-notch fa-spin'></i></div>");
    $(".ui-state-modal").hide();

      var sent = $(e.target).hasClass("resend");
      var date_left = $('#espaceprive_date_left_rec').val();
      var email = [];
      $('input[name^="espaceprive_email_client"]').each(function(i) {
        email[i] = $(this).val();
      });

        fd2 = new FormData();
        fd2.append('nonce_ajax', nonce_ajax);
        fd2.append("action", "pic_template_sent_gallery");
        fd2.append('post_id', post_id);
        fd2.append('emails', email);
        fd2.append('post_password', $('#post_password').val());
        fd2.append('sent', sent);
        fd2.append('date_left', date_left);
        fd2.append('act', 'step_2');
        var ajax = await $.ajax({
          url: ajaxurl,
          type: "POST",
          contentType: false,
          processData: false,
          data: fd2,
        });

        $.when(ajax).done(function () {
          setTimeout(function(){
            $("#modal_before_publish").html(ajax);
            $("#publish").unbind('click').click(); 
          }, 2000);
        });
    });

    
    var popup = $("#modal_before_publish").dialog({
        autoOpen: true,
        width: 400,
        dialogClass: 'dialog_before_publish',
        close: function( event, ui ) {
          $('#pic_overlay').remove();
          $('#modal_before_publish').remove();  
          $('body, html').css({'overflow':'inherit', 'max-height':'inherit'});        
        },
        buttons: [
            {
                text: $("#publish").val(),
                "class": 'ui-state-modal',
                click: function () {
               //     $(this).dialog("close");
                //    $('#pic_overlay').remove();
                   // $('#modal_before_publish').hide();
                    $('body, html').css({'overflow':'inherit', 'max-height':'inherit'});
                    if(!mailsend){
                        $('<div></div>').appendTo('body')
                        .html('<div><p>'+__('Are you send a email to a client', 'pic_sell_plugin')+' ?</p></div>')
                        .dialog({
                          modal: true,
                          title: __('Send email', 'pic_sell_plugin'),
                          zIndex: 10000,
                          autoOpen: true,
                          width: 'auto',
                          resizable: false,
                          buttons: {
                            Yes: function() {
                              $(".ps_send_gallery").click();
                              $(this).dialog("close");
                            },
                            No: function() {
                              $("#publish").unbind('click').click();
                              $(this).dialog("close");
                            }
                          },
                          close: function(event, ui) {
                            $(this).remove();
                          }
                        });
                    }else{
                      $("#publish").unbind('click').click();
                    }
                        
                }
            }
        ]
    });

    $("#modal_before_publish").on("click", ".ps_reset_sent_dateleft", function(){

      e.preventDefault();
  
      var post_id = PicSellVars.post.ID;
      var post_title = PicSellVars.post.post_title;
      var nonce_ajax = PicSellVars.nonce;
  
      fd2 = new FormData();
      fd2.append("action", "pic_template_sent_gallery");
      fd2.append('nonce_ajax', nonce_ajax);
      fd2.append('post_id', post_id);
      fd2.append('act', 'reset_sent_dateleft');
  
      var ajax = $.ajax({
        url: ajaxurl,
        type: "POST",
        contentType: false,
        processData: false,
        data: fd2,
        success:function( data ) {
          $("#modal_before_publish").dialog('close');
          $("#publish").click();
        },
      });
  
  
  
  
    });

    console.log("intercept");
    //
  });

});
