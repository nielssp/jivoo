$(function() {
  var $jsonTags = $('#Post_jsonTags');
  var addTag = function() {
    var tag = $('#Post_addTag').val();
    var name = tag.toLowerCase().replace(/[^a-z0-9 ]/g, '').replace(/ /g, '-');
    var $tag = $('<span class="tag"></span>')
      .html(tag)
      .data('name', name)
      .append(' <a href="#" class="tag-remove"><span class="icon-remove"></span></a>')
      .appendTo('.tags');
    var tags = JSON.parse($jsonTags.val());
    tags[name] = tag;
    $jsonTags.val(JSON.stringify(tags));
    $('#Post_addTag').val('');
  };
  $('#Post_addTag_button').click(addTag);
  $('#Post_addTag').keydown(function (e) {
    if (e.keyCode == 13){
      addTag();
      return false;
    }
  })
  
  $('.tags').on('click', '.tag-remove', function() {
    var $tag = $(this).parent();
    var tags = JSON.parse($jsonTags.val());
    delete tags[$tag.data('name')];
    $jsonTags.val(JSON.stringify(tags));
    $tag.remove();
  });
});