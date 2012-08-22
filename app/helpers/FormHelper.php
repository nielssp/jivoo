<?php

class FormHelper extends ApplicationHelper {

  private $record = null;
  private $currentForm = '';
  private $post = false;
  private $errors = array();
  
  public function begin(IModel $record = null, $fragment = null) {
    if (!isset($record)) {
      return false;
    }
    $this->post = $this->request->isPost();
    $this->record = $record;
    if ($this->post) {
      $this->errors = $record->getErrors();
    }
    $this->currentForm = $record->getName();
    $html = '<form action="' . $this->getLink(array('fragment' => $fragment))
      . '" id="' . $this->currentForm . '" method="post">' . PHP_EOL;
    $html .= '<input type="hidden" name="access_token" value="'
      . $this->request->getToken() . '" />' . PHP_EOL;
    foreach ($this->record->getFields() as $field) {
      if ($this->record->getFieldType($field) == 'hidden') {
        $html .= $this->hidden($field);
      }
    }
    return $html;
  }

  public function fieldName($field) {
    if (!isset($this->record)) {
      return;
    }
    return $this->currentForm . '[' . $field . ']';
  }

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
  
  public function isRequired($field, $output = null) {
    if (!isset($this->record)) {
      return;
    }
    $required = $this->record->isFieldRequired($field);
    if (isset($output)) {
      return $required ? $output : '';
    }
    else {
      return $required;
    }
  }
  
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
  
  public function getErrors() {
    if (!isset($this->record)) {
      return;
    }
    return $this->errors;
  }
  
  public function getError($field) {
    if (!isset($this->record)) {
      return;
    }
    return isset($this->errors[$field]) ? $this->errors[$field] : '';
  }

  public function label($field, $label = null,  $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<label for="' . $this->fieldId($field) . '"';
    if (isset($options['class'])) {
      $html .= ' class="' . $options['class'] . '"';
    }
    if (!isset($label)) {
      $label = $this->record->getFieldLabel($field);
    }
    $html .= '>' . $label . '</label>' . PHP_EOL;
    return $html;
  }

  public function field($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $editor = $this->record->getFieldEditor($field);
    if (isset($editor)) {
      return $editor->field($this, $field, $options);
    }
    $type = $this->record->getFieldType($field);
    switch ($type) {
      case 'text':
        return $this->textarea($field, $options);
      case 'hidden':
        return $this->hidden($field, $options);
      default:
        if (strpos($field, 'pass') !== false) {
          return $this->password($field, $options);
        }
        return $this->text($field, $options);
    }
  }

  public function fieldValue($field, $encode = true) {
    if (!isset($this->record)) {
      return;
    }
    $editor = $this->record->getFieldEditor($field);
    if (isset($editor)) {
      $format = $editor->getFormat();
      if ($encode) {
        return h($format->fromHtml($this->record->$field));
      }
      else {
        return $format->fromHtml($this->record->$field);
      }
    }
    if (isset($this->record->$field)) {
      if ($encode) {
        return h($this->record->$field);
      }
      else {
        return $this->record->$field;
      }
    }
    return '';
  }

  private function addAttributes($options) {
    $html = '';
    foreach ($options as $attribute => $value) {
      $html .= ' ' . $attribute . '="' . h($value) . '"';
    }
    return $html;
  }

  public function hidden($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<input type="hidden" name="' . $this->fieldName($field) .'"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    $html .= $this->addAttributes($options);
    if ($this->fieldValue($field) != '') {
      $html .= ' value="' . $this->fieldValue($field) . '"';
    }
    $html .= ' />' . PHP_EOL;
    return $html;
  }

  public function text($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<input type="text" name="' . $this->fieldName($field) .'"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    if (!isset($options['class'])) {
      $options['class'] = 'text';
    }
    $html .= $this->addAttributes($options);
    if ($this->fieldValue($field) != '') {
      $html .= ' value="' . $this->fieldValue($field) . '"';
    }
    $html .= ' />' . PHP_EOL;
    return $html;
  }

  public function radio($field, $value, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<input type="radio" name="' . $this->fieldName($field) .'"';
    $html .= ' id="' . $this->fieldId($field, $value) . '"';
    $html .= ' value="' . $value . '"';
    $html .= $this->addAttributes($options);
    if ($this->fieldValue($field) == $value) {
      $html .= ' checked="checked"';
    }
    $html .= ' /> ' . PHP_EOL;
    return $html;
  }

  public function password($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<input type="password" name="' . $this->fieldName($field) .'"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    if (!isset($options['class'])) {
      $options['class'] = 'text';
    }
    $html .= $this->addAttributes($options);
    $html .= ' />' . PHP_EOL;
    return $html;
  }

  public function textarea($field, $options = array()) {
    if (!isset($this->record)) {
      return;
    }
    $html = '<textarea name="' . $this->fieldName($field) .'"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    $html .= $this->addAttributes($options);
    $html .= '>';
    if ($this->fieldValue($field) != '') {
      $html .= $this->fieldValue($field);
    }
    $html .= '</textarea>' . PHP_EOL;
    return $html;
  }

  public function submit($value, $field = 'submit', $options = array()) {
    if (!isset($this->record)) {
      return;
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
    
  public function end() {
    $this->record = null;
    $this->errors = array();
    return '</form>';
  }
}
