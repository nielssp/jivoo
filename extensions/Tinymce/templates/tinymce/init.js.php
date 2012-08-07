$(function() {
  $('textarea.tinymce').tinymce({
    script_url : "<?php echo $scriptUrl; ?>",

    // General options
    theme : "advanced",
    skin : "cirkuit",
    plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,emotions,iespell,jqueryinlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

    // Theme options
  //    theme_advanced_buttons1 : "save,cancel,|,spellchecker,preview,|,cut,copy,paste,pastetext,pasteword,|,undo,redo,|,search,replace,|,table,link,unlink,image,|,cleanup,code,help,fullscreen",
    theme_advanced_buttons1 : "formatselect,|,bold,italic,underline,strikethrough,|,,justifyleft,justifycenter,justifyright,|,numlist,bullist,|,link,unlink,pagebreak,charmap,|,undo,redo,|,code,fullscreen,help",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",
  //    theme_advanced_buttons2 : "formatselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,numlist,bullist,outdent,indent",
  //    theme_advanced_buttons3 : "examplesbutton,blockquote,|,sub,sup,|,removeformat,|,insertdate,inserttime,|,charmap,emotions,media,|,restoredraft",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_statusbar_location : "bottom",
    theme_advanced_resize_horizontal : false,
    theme_advanced_resizing : true,

    // Example content CSS (should be your site CSS)
    content_css : "<?php echo $styleUrl; ?>",

  });
});
