/* 
 * PeanutCMD backend javascript
 */

$(function() {

  String.prototype.trim = function() {
    return this.replace(/(^[ \n\t\r]+|[ \n\r\t]+$)/g, '');
  }

  $.maxZIndex = $.fn.maxZIndex = function(opt) {
    /// <summary>
    /// Returns the max zOrder in the document (no parameter)
    /// Sets max zOrder by passing a non-zero number
    /// which gets added to the highest zOrder.
    /// </summary>
    /// <param name="opt" type="object">
    /// inc: increment value,
    /// group: selector for zIndex elements to find max for
    /// </param>
    /// <returns type="jQuery" />
    var def = { inc: 10, group: "*" };
    $.extend(def, opt);
    var zmax = 0;
    $(def.group).each(function() {
      var cur = parseInt($(this).css('z-index'));
      zmax = cur > zmax ? cur : zmax;
    });
    if (!this.jquery)
      return zmax;

    return this.each(function() {
      zmax += def.inc;
      $(this).css("z-index", zmax);
    });
  }
  
  $.fn.menuButton = function() {
    this.each(function() {
      var items = $(this).find("li:not(.first)");
      $(this).bind('mouseover mouseout', function (event) {
        if (event.type == 'mouseover') {
          items.show();
          $(this).parent().maxZIndex();
        } else {
          items.hide();
        }
      });
    });
  }

  $(".button").button();

  $(document).bind('keydown', 'esc', function () {
    $(".menubar").find(".items").hide();
  });

  var over = false;
  var inputFocus = false;
  
  $(":text, :[type=search], textarea").bind("blur focus", function(event) {
    inputFocus = event.type == "focus";
  });
  
  $(document).bind('click', function () {
    if (!over)
      $(".menubar").find(".items").hide();
  });
  
  var rootShortcuts = [];
  var allShortcuts = [];
  var shortcuts = {};
  
  $(".item a").each(function() {
    var category = $(this).data("shortcut-on");
    var label = $.trim($(this).html());
    var chars = label + "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"; // Alternatives 
    var shortcut = null;
    var found = false;
    for (c in chars) {
      shortcut = chars[c].toUpperCase();
      if ($.inArray(shortcut, rootShortcuts) > -1) {
        continue;
      }
      if (category == 'root') {
        if ($.inArray(shortcut, allShortcuts) > -1) {
          continue;
        }
        rootShortcuts.push(shortcut);
      }
      else {
        if (category in shortcuts && $.inArray(shortcut, shortcuts[category]) > -1) {
          continue;
        }
        if (!(category in shortcuts)) {
          shortcuts[category] = [];
        }
        shortcuts[category].push(shortcut);
      }
      allShortcuts.push(shortcut);
      found = true;
      break;
    }    
    if (found) {
      $(this).data("shortcut", shortcut);
    }
  });
  
  $(".item a").each(function() {
    var shortcut = $(this).data("shortcut");
    var label = $(this).html();
    var link = $(this);
    if (shortcut == undefined)
      return;
    $(this).prepend('<div class="key">' + shortcut + '</div>');
    if (!$(this).parent().hasClass('header')) {
      var items = $(this).parent().parent();
      var keystate = false;
      $(document).bind('keydown keyup', shortcut, function(event) {
        if (keystate && event.type == 'keydown')
          return;
        if (!keystate && event.type == 'keyup')
          return;
        keystate = event.type == 'keydown';
        if (!keystate)
          return;
        if (items.css('display') != "none") {
//          console.log("Pressed " + shortcut + ": " + label);
          window.location.href = link.attr("href");
        }
      });
    }
  });
  
  $(".menubutton").menuButton();

  $(".menu").each(function() {
    var items = $(this).find(".items");
    var key = $(this).find(".header a").data("shortcut");
    $(this).bind('mouseover mouseout', function (event) {
      if (event.type == 'mouseover') {
        over = true;
        items.parent().parent().find(".items").hide();
        items.show().maxZIndex();
      } else {
        over = false;
        items.hide();
      }
    });
//    console.log("Binding " + key + "...");
    var keystate = false;
    $(document).bind('keydown keyup', key, function (event) {
      if (keystate && event.type == 'keydown')
        return;
      if (!keystate && event.type == 'keyup')
        return;
      keystate = event.type == 'keydown';
      if (!keystate)
        return;
//      console.log("Pressed " + key);
      if (inputFocus)
        return;
      if (items.css('display') == "none") {
        items.parent().parent().find(".items").hide();
        items.show().maxZIndex();
        items.find('.item:first').find('a').focus();
      }
      else {
        items.parent().parent().find(".items").hide();
      }
    });
  });
  
  $(".radioset, .buttonset").buttonset();
  
  $("#check_settings").bind("change", function() {
    if (this.checked)
      $("#settings").show("blind", 200);
    else
      $("#settings").hide("blind", 200);
  });

  $(".approve-action, .unapprove-action, .spam-action").live('click', function() {
    var status = 'pending';
    var targetClass = 'yellow';
    if ($(this).hasClass('approve-action')) {
      status = 'approved';
      targetClass = null;
    }
    else if ($(this).hasClass('spam-action')) {
      status = 'spam';
      targetClass = 'red';
    }
    var action = $(this).attr('href');
    var record = $(this).parents(".record");
    var accessToken = $('input[name=access_token]').val();
    $.ajax({
      type: 'POST',
      url: action,
      dataType: 'json',
      data: {
        access_token: accessToken,
        comment: { status: status }
      },
      success: function(data) {
        if (record.hasClass('yellow')) {
          record.removeClass('yellow', 500);
        }
        else if (record.hasClass('red')) {
          record.removeClass('red', 500);
        }
        if (targetClass != null) {
          record.addClass(targetClass, 500);
        }
        record.html($(data.html).html());
        record.find('.menubutton').menuButton();
      }
    });
    return false;
  });

  $(".delete-action").live('click', function() {
    $(this).parents(".record").each(function() {
      $(this).animate({opacity: '0'}, 300).slideUp(200, function() {
        if ($(this).hasClass('first')) {
          $(this).nextAll(':visible:first').addClass('first');
        }
        var list = $(this).parent();
        $(this).remove();
        $.ajax({
          type: 'GET',
          url: location.href,
          dataType: 'json',
          data: {
            filter: $('input[name=filter]').val(),
            from: $('input[name=to]').val(),
            to: $('input[name=to]').val()
          },
          success: function(data) {
            list.append(data.html);
          }
        });
      });
    });
    return false;
  });
  

  $(".permalink").each(function(index, Element) {
    var input = $(this);
    var titleId = input.data('title-id');
    var allowSlash = input.hasClass('permalink-allow-slash');
    var posted = false;
    var title = null;
    if (titleId) {
      title = $("#" + titleId)
      title.keyup(function() {
        var title = $(this).val();
        if (allowSlash)
          title = title.replace(/[^(a-zA-Z0-9 \-\/)]/g, "").replace(/[ \-]/g, "-").toLowerCase();
        else
          title = title.replace(/[^(a-zA-Z0-9 \-)]/g, "").replace(/[ \-]/g, "-").toLowerCase();
        input.val(title);
      });
    }
    input.bind("blur focus", function(event) {
      if (event.type == "focus")
        input.parent().addClass("permalink-wrapper-focus");
      else
        input.parent().removeClass("permalink-wrapper-focus");
    });
    input.parent().click(function() {
      input.focus();
    });
  });
  
  $("input#login_username").each(function(index, Element) {
    if ($(this).val() == '') {
      $(this).focus();
    }
    else {
      $("input#login_password").focus();
    }
  });

  $("#pagination").each(function() {
    var pagination = $(this);
    var filterInput = pagination.find('input[name=filter]');
    var from = pagination.data('from');
    var to = pagination.data('to');
    var count = pagination.data('count');

    var phrases = [];
    
    $(".bulk-actions :checkbox").each(function(index, Element) {
      var checkbox = $(this);
      var label = checkbox.parent().parent().find('label');
      phrases[0] = label.html();
      phrases[1] = label.data('phrase1');
      phrases[2] = label.data('phrase2');
      label.html(phrases[1]);
      checkbox.val('not-all');
      checkbox.change(function() {
        checkbox.val('not-all');
        if ($(this).attr('checked') == 'checked') {
          $(".record :checkbox").attr('checked', 'checked').change();
          $(".bulk-actions :checkbox").attr('checked', 'checked');
        }
        else {
          $(".record :checkbox").removeAttr('checked').change();
          $(".bulk-actions :checkbox").removeAttr('checked');
        }
      });
    });

    var selectAllLink = $('<a></a>').html('(' + phrases[0].trim() + ')');
    selectAllLink.attr('href', '#');

    var labels = $('.bulk-actions .checkbox-text label');

    labels.find('a').live('click', function() {
      $(".bulk-actions :checkbox").val('all');
      labels.html(phrases[2].replace('0', count));
    });

    $(".record :checkbox").change(function() {
      var total = $(".record :checkbox").length;
      var number = $(".record :checkbox:checked").length;
      if (number == 0) {
        var text = phrases[1];
      }
      else {
        var text = phrases[2].replace('0', number);
      }
      labels.html(text);
      if (number == total) {
        labels.append(selectAllLink);
      }
    });

//    $(".bulk-actions :checkbox").change(function() {
//      if ($(this).attr('checked') == 'checked') {
//        $(".record :checkbox").attr('checked', 'checked').change();
//        $(".bulk-actions :checkbox").attr('checked', 'checked');
//      }
//      else {
//        $(".record :checkbox").removeAttr('checked').change();
//        $(".bulk-actions :checkbox").removeAttr('checked');
//      }
//    });

  });
  
});
