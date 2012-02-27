/* 
 * PeanutCMD backend javascript
 */

(function($) {
  $.fn.simpleDataTable = function(options) {
    var defaults = {
      css : 'backend-data-table'
    };
    options = $.extend(defaults, options);

    return this.each(function() {

      input = $(this);
      input.addClass(options.css);

      input.find("tbody tr").live('mouseover mouseout', function(event) {
        if (event.type == 'mouseover') {
          $(this).children("td").addClass("ui-state-hover");
        }
        else {
          $(this).children("td").removeClass("ui-state-hover");
        }
      });

      input.find("th").addClass("ui-state-default");
      input.find("td").addClass("ui-widget-content");

    });
  };
})(jQuery);

$(function() {

  $(".backend-data-table").simpleDataTable();

  $("#backend-toolbar a, #backend-page button, a.backend-button").each(
      function(index, Element) {
        var icon = $(this).attr('rev');
        if (icon)
          $(this).button({
            icons : {
              primary : icon
            }
          });
        else
          $(this).button();
      });

  $(".backend-sortable").sortable({
    scroll : false,
    axis : 'y'
  });

  $(".backend-radioset, .backend-buttonset").buttonset();

  $("#backend-toolbar span.menu").buttonset().children().next().button(
      "option", {
        text : false
      });

  $("#backend-page-close").button({
    icons : {
      primary : "ui-icon-close"
    },
    text : false
  }).removeClass('ui-button').removeClass('ui-state-default').addClass(
      'ui-dialog-titlebar-close');

  $(".backend-logout").button('option', 'text', false);

  $(".backend-global-error").button({
    icons : {
      primary : "ui-icon-alert"
    },
    text : false
  }).addClass("ui-state-error");

  $(".backend-global-notice").button({
    icons : {
      primary : "ui-icon-info"
    },
    text : false
  }).addClass("ui-state-highlight");

  $(".backend-datepicker").datepicker($.datepicker.regional['da']);

  $(".backend-format-radioset").each(function() {
    $(this).find(".backend-radio").each(function() {
      var label = $(this);
      label.find("input").click(function() {
        label.parent().find("input.text-inline").val(label.attr('title'));
      });
    });
  });

  $(".backend-permalink").each(
      function(index, Element) {
        var input = $(this);
        var titleId = input.attr('rev');
        var allowSlash = input.hasClass('backend-permalink-allow-slash');
        var posted = false;
        var title = null;
        if (titleId) {
          title = $("#" + titleId)
          title.keyup(function() {
            var title = $(this).val();
            if (allowSlash)
              title = title.replace(/[^(a-zA-Z0-9 \-\/)]/g, "").replace(
                  /[ \-]/g, "-").toLowerCase();
            else
              title = title.replace(/[^(a-zA-Z0-9 \-)]/g, "").replace(/[ \-]/g,
                  "-").toLowerCase();
            input.val(title);
          });
        }
        var icon;
        if (!titleId
            || (title.val() != '' && input.val() != '' && input.val() != title
                .val())) {
          icon = "ui-icon-unlocked";
          input.keyup(function() {
            var title = input.val();
            if (allowSlash)
              title = title.replace(/[^(a-zA-Z0-9 \-\/)]/g, "").replace(
                  /[ \-]/g, "-").toLowerCase();
            else
              title = title.replace(/[^(a-zA-Z0-9 \-)]/g, "").replace(/[ \-]/g,
                  "-").toLowerCase();
            input.val(title);
          });
        }
        else {
          icon = "ui-icon-locked";
          input.attr('disabled', 'disabled');
          input.addClass('ui-state-disabled');
        }
        input.parent().parent().children(".backend-permalink-unlock").button({
          icons : {
            primary : "ui-icon-locked"
          },
          text : false
        }).click(
            function() {
              var attr = input.attr('disabled');
              if (attr == 'disabled') {
                input.removeAttr('disabled');
                input.removeClass('ui-state-disabled');
                $(this).button("option", {
                  icons : {
                    primary : "ui-icon-unlocked"
                  }
                });
                input.keyup(function() {
                  var title = input.val();
                  if (allowSlash)
                    title = title.replace(/[^(a-zA-Z0-9 \-\/)]/g, "").replace(
                        /[ \-]/g, "-").toLowerCase();
                  else
                    title = title.replace(/[^(a-zA-Z0-9 \-)]/g, "").replace(
                        /[ \-]/g, "-").toLowerCase();
                  input.val(title);
                });
              }
              else {
                input.attr('disabled', 'disabled');
                input.addClass('ui-state-disabled');
                $(this).button("option", {
                  icons : {
                    primary : "ui-icon-locked"
                  }
                });
              }
              return false;
            });
      });

  $('textarea.backend-wysiwyg')
      .tinymce(
          {
            // Location of TinyMCE script
            /**
             * @todo FIX!!
             */
            script_url : WEBPATH + INC + "js/tinymce/tiny_mce.js",

            // General options
            theme : "advanced",
            skin : "thebigreason",
            plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,emotions,iespell,jqueryinlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

            // Theme options
            // theme_advanced_buttons1 :
            // "save,cancel,|,spellchecker,preview,|,cut,copy,paste,pastetext,pasteword,|,undo,redo,|,search,replace,|,table,link,unlink,image,|,cleanup,code,help,fullscreen",
            theme_advanced_buttons1 : "formatselect,|,bold,italic,underline,strikethrough,|,,justifyleft,justifycenter,justifyright,|,numlist,bullist,|,link,unlink,pagebreak,charmap,|,undo,redo,|,code,fullscreen,help",
            theme_advanced_buttons2 : "",
            theme_advanced_buttons3 : "",
            // theme_advanced_buttons2 :
            // "formatselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,numlist,bullist,outdent,indent",
            // theme_advanced_buttons3 :
            // "examplesbutton,blockquote,|,sub,sup,|,removeformat,|,insertdate,inserttime,|,charmap,emotions,media,|,restoredraft",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resize_horizontal : false,
            theme_advanced_resizing : true,

            // Example content CSS (should be your site CSS)
            content_css : WEBPATH + INC + "js/tinymce/css/content.css",

            // Drop lists for link/image/media/template dialogs
            template_external_list_url : "lists/template_list.js",
            external_link_list_url : "lists/link_list.js",
            external_image_list_url : "lists/image_list.js",
            media_external_list_url : "lists/media_list.js",

            // Replace values for the template plugin
            template_replace_values : {
              username : "Some User",
              staffid : "991234"
            }
          });

});