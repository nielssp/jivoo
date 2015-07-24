$(function() {
  var $fields = $('#schema-fields');
  var $fieldTemplate = $fields.find('tr').first();
  
  $fields.find('tr').remove();
  
  $('#add-field').click(function() {
    var $field = $fieldTemplate.clone();
    $fields.append($field);
    $field.find('input').first().focus();
    $field.find('[data-remove]').click(function() {
      $field.remove();
    });
    return false;
  });
});