<?php
/**
 * A helper for creating HTML forms
 * @package Core\Helpers
 */
class FormHelper extends Helper {

  /**
   * @var IRecord Associated record
   */
  private $record = null;
  
  /**
   * @var IModel Associated model
   */
  private $model = null;
  
  /**
   * @var string Name of current form
   */
  private $currentForm = '';
  
  /**
   * @var bool Whether or not current request is post, i.e. data was submitted
   */
  private $post = false;
  
  /**
   * @var array Associative array of field names and error messages
   */
  private $errors = array();

  /**
   * Begin a new form, returned HTML must be outputted to page. Remember to
   * end the form with {@see FormHelper::end()}.
   * @param IRecord $record Record to base form on
   * @param string $fragment Fragment of page to return to i.e. 'create-comment'
   * to append '#create-comment' to form action
   * @param array|ILinkable|string|null $route Route to submit form to, default is current page. See
   * {@see Routing}.
   * @param array $options An associative array of options, the only supported
   * option is 'class', which sets the class-attribute of the form-tag
   * @return string HTML code
   */
  public function begin(IRecord $record = null, $fragment = null, $route = array(), $options = array()) {
    if (!isset($record)) {
      $record = new Form('form');
    }
    $this->post = $this->request->isPost();
    $this->record = $record;
    $this->model = $record->getModel();
    if ($this->post) {
      $this->errors = $record->getErrors();
    }
    $this->currentForm = $this->model->getName();
    $route['fragment'] = $fragment;
    $html = '<form action="' . $this->getLink($route)
      . '" id="' . $this->currentForm . '" method="post"';
    if (isset($options['class'])) {
      $html .= ' class="' . $options['class'] . '"';
    }
    $html .= '>' . PHP_EOL;
    $html .= $this->request->createHiddenToken() . PHP_EOL;
    foreach ($this->model->getFields() as $field) {
      if ($this->model->getFieldType($field) == 'hidden') {
        $html .= $this->hidden($field);
      }
    }
    return $html;
  }

  /**
   * Get full name of form field, will be FORM[FIELD] where FORM is the name of
   * the form.
   * @param string $field Field name in model
   * @return string Name of form field
   */
  public function fieldName($field) {
    if (!isset($this->record)) {
      return;
    }
    return $this->currentForm . '[' . $field . ']';
  }

  /**
   * Get id of form field, will be FORM_FIELD or FORM_FIELD_VALUE for
   * checkboxes and radios, where FORM is the name of the form.
   * @param string $field Field name in model
   * @param string $value Optional value for field (for checkboxes and radios)
   * @return string Id of form field
   */
  public function fieldId($field, $value = null) {
    if (!isset($this->record)) {
      return;
    }
    $id = $this->currentForm . '_' . $field;
    if (isset($value)) {
      $id .= '_' . $value;
    }
    return $id;
  }

  /**
   * Check whether or not a field is required, i.e. must have a non-empty value 
   * @param string $field Field name
   * @param string|null $output If set, instead of returning a boolean, the
   * value of $output will be returned if the field is required, and an empty
   * string will be returned if the field is not required.
   * @return string|boolean If $output is not set, then true will be
   * returned if the field is required, and false otherwise. If $output is set,
   * the value of $output is returned if field is required, and '' otherwise.
   */
  public function isRequired($field, $output = null) {
    if (!isset($this->record)) {
      return;
    }
    $required = $this->model->isFieldRequired($field);
    if (isset($output)) {
      return $required ? $output : '';
    }
    else {
      return $required;
    }
  }

  /**
   * Check whether or not a field is optional.
   * @see FormHelper::isRequired();
   * @param unknown $field Field name
   * @param string $output Optional output to return instead of boolean.
   * @return string|boolean If $output is not set, then true will be
   * returned if the field is optional, and false otherwise. If $output is set,
   * the value of $output is returned if field is optional, and '' otherwise.
   */
  public function isOptional($field, $output = null) {
    if (!isset($this->record)) {
      return;
    }
    $required = $this->model->isFieldRequired($field);
    if (isset($output)) {
      return $required ? '' : $output;
    }
    else {
      return !$required;
    }
  }

  /**
   * Check whether or not a field, or the entire form, is valid
   * @param string $field Name of field to check. If not set, the entire form
   * is checked.
   * @return boolean True if field/form is valid, false otherwise
   */
  public function isValid($field = null) {
    if (!isset($this->record)) {
      return;
    }
    if (!$this->post) {
      return true;
    }
    if (isset($field)) {
      return !isset($this->errors[$field]);
    }
    else {
      return count($this->errors) < 1;
    }
  }

  /**
   * Get all errors
   * @return array An associative array of field names and error messages
   */
  public function getErrors() {
    if (!isset($this->record)) {
      return;
    }
    return $this->errors;
  }

  /**
   * Get the error message for a specific field
   * @param string $field Field name
   * @return string An error message if it exists, or an empty string
   * otherwise
   */
  public function getError($field) {
    if (!isset($this->record)) {
      return;
    }
    return isset($this->errors[$field]) ? $this->errors[$field] : '';
  }

  /**
   * Create a label for a field
   * @param string $field Field name
   * @param string $label Custom label content, if not set it will be
   * retrieved from the model.
   * @param array $options An associative array of options, the only supported
   * option is 'class', which sets the class-attribute of the label-tag
   * @return string HTML code
   */
  public function label($field, $label = null, $options = array()) {
    return $this->radioLabel($field, null, $label, $options);
  }

  /**
   * Create a label for a radio field
   * @param string $field Field name
   * @param string $value Field value
   * @param string $label Custom label content, if not set it will be
   * retrieved from the model.
   * @param array $options An associative array of options, the only supported
   * option is 'class', which sets the class-attribute of the label-tag
   * @return string HTML code
   */
  public function radioLabel($field, $value, $label = null, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<label for="' . $this->fieldId($field, $value) . '"';
    if (isset($options['class'])) {
      $html .= ' class="' . $options['class'] . '"';
    }
    if (!isset($label)) {
      $label = $this->model->getFieldLabel($field);
    }
    $html .= '>' . $label . '</label>' . PHP_EOL;
    return $html;
  }

  /**
   * Create a label for a checkbox field
   * @param string $field Field name
   * @param string $label Custom label content, if not set it will be
   * retrieved from the model.
   * @param array $options An associative array of options, the only supported
   * option is 'class', which sets the class-attribute of the label-tag
   * @return string HTML code
   */
  public function checkboxLabel($field, $value, $label = null, $options = array()) {
    return $this->radioLabel($field, $value, $label, $options);
  }

  /**
   * Create a field automatically. If an editor is set for that field, the
   * editor will be returned. If the field type is 'text', a textarea will be
   * returned. If the field name has 'pass' in it, a password field will be
   * returned. If the field name has 'date' in it, a date field will be
   * returned. Otherwise a regular text field is returned.
   * @param string $field Field name
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return vois|string HTML code
   */
  public function field($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $editor = $this->model->getFieldEditor($field);
    if (isset($editor)) {
      return $editor->field($this, $field, $options);
    }
    $type = $this->model->getFieldType($field);
    switch ($type) {
      case 'text':
        return $this->textarea($field, $options);
      case 'hidden':
        return $this->hidden($field, $options);
      default:
        if (strpos($field, 'pass') !== false) {
          return $this->password($field, $options);
        }
        if (strpos($field, 'date') !== false) {
          return $this->date($field, $options);
        }
        return $this->text($field, $options);
    }
  }

  /**
   * Get the HTML encoded value of a field
   * @param string $field Field name
   * @param bool $encode Whether or not to encode, default is true
   * @return string Field value
   */
  public function fieldValue($field, $encode = true) {
    if (!isset($this->record)) {
      return;
    }
    if ($this->model->getFieldType($field) == 'date') {
      return fdate($this->record->$field);
    }
    $editor = $this->model->getFieldEditor($field);
    if (isset($editor)) {
      $format = $editor->getFormat();
      if ($encode) {
        return h($format->fromHtml($this->record->$field));
      }
      else {
        return $format->fromHtml($this->record->$field);
      }
    }
    if ($this->model->isField($field)) {
      if ($encode) {
        return h($this->record->$field);
      }
      else {
        return $this->record->$field;
      }
    }
    return '';
  }

  /**
   * Add additional attributes
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  private function addAttributes($options) {
    $html = '';
    foreach ($options as $attribute => $value) {
      $html .= ' ' . $attribute . '="' . h($value) . '"';
    }
    return $html;
  }

  /**
   * Create a hidden field
   * @param string $field Field name
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  public function hidden($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<input type="hidden" name="' . $this->fieldName($field) . '"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    $html .= $this->addAttributes($options);
    if ($this->fieldValue($field) != '') {
      $html .= ' value="' . $this->fieldValue($field) . '"';
    }
    $html .= ' />' . PHP_EOL;
    return $html;
  }

  /**
   * A text field. The default class-attribute is 'text'.
   * @param string $field Field name
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  public function text($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<input type="text" name="' . $this->fieldName($field) . '"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    if (!isset($options['class'])) {
      $options['class'] = 'text';
    }
    $html .= $this->addAttributes($options);
    $value = $this->fieldValue($field); 
    if ($value != '') {
      $html .= ' value="' . $value . '"';
    }
    $html .= ' />' . PHP_EOL;
    return $html;
  }

  /**
   * A date field. The default class-attribute is 'text date'.
   * @param string $field Field name
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  public function date($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<input type="date" name="' . $this->fieldName($field) . '"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    if (!isset($options['class'])) {
      $options['class'] = 'text date';
    }
    $html .= $this->addAttributes($options);
    $value = $this->fieldValue($field); 
    if ($value != '') {
      $html .= ' value="' . fdate($value) . '"';
    }
    $html .= ' />' . PHP_EOL;
    return $html;
  }

  /**
   * A radio field.
   * @param string $field Field name
   * @param mixed $value Field value 
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  public function radio($field, $value, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<input type="radio" name="' . $this->fieldName($field) . '"';
    $html .= ' id="' . $this->fieldId($field, $value) . '"';
    $html .= ' value="' . $value . '"';
    $html .= $this->addAttributes($options);
    if ('' . $this->fieldValue($field) == "$value") {
      $html .= ' checked="checked"';
    }
    $html .= ' /> ' . PHP_EOL;
    return $html;
  }
  
  /**
   * A select field.
   * @param string $field Field name
   * @param array $values An associative array of values and labels
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  public function select($field, $values = array(), $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<select name="' . $this->fieldName($field) . '"';
    $html .= ' id="' . $this->fieldId($field, $value) . '"';
    $html .= $this->addAttributes($options);
    $html .= '>' . PHP_EOL;
    foreach ($values as $value => $label) {
      $html .= '<option value="' . h($value) . '"';
      if ('' . $this->fieldValue($field) == "$value") {
        $html .= ' selected="selected"';
      }
      $html .= '>' . h($label) . '</option>' . PHP_EOL;
    }
    $html .= '</select>' . PHP_EOL;
    return $html;
  }
  
  /**
   * A checkbox.
   * @param string $field Field name
   * @param mixed $value Field value
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  public function checkbox($field, $value, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<input type="checkbox" name="' . $this->fieldName($field) . '"';
    $html .= ' id="' . $this->fieldId($field, $value) . '"';
    $html .= ' value="' . $value . '"';
    $html .= $this->addAttributes($options);
    if ($this->fieldValue($field) == $value) {
      $html .= ' checked="checked"';
    }
    $html .= ' /> ' . PHP_EOL;
    return $html;
  }

  /**
   * A password field. The default class-attribute is 'text'.
   * @param string $field Field name
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  public function password($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<input type="password" name="' . $this->fieldName($field) . '"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    if (!isset($options['class'])) {
      $options['class'] = 'text';
    }
    $html .= $this->addAttributes($options);
    $html .= ' />' . PHP_EOL;
    return $html;
  }

  /**
   * A textarea.
   * @param string $field Field name
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  public function textarea($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<textarea name="' . $this->fieldName($field) . '"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    $html .= $this->addAttributes($options);
    $html .= '>';
    $html .= $this->fieldValue($field);
    $html .= '</textarea>' . PHP_EOL;
    return $html;
  }

  /**
   * A submit button. The default class-attribute is 'button'.
   * @param string $value Value, default is 'Submit'
   * @param string $field Field name, default is 'submit'
   * @param array $options An associative array of additional attributes to add
   * to field
   * @return string HTML code
   */
  public function submit($value = null, $field = 'submit', $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    if (!isset($value)) {
      $value = tr('Submit');
    }
    $html = '<input type="submit" name="' . $field . '"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    $html .= ' value="' . $value . '"';
    if (!isset($options['class'])) {
      $options['class'] = 'button';
    }
    $html .= $this->addAttributes($options);
    $html .= ' /> ' . PHP_EOL;
    return $html;
  }

  /**
   * End the form.
   * @return string HTML code
   */
  public function end() {
    $this->record = null;
    $this->model = null;
    $this->errors = array();
    return '</form>';
  }
}
