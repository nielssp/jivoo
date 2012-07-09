<?php

class FormHelper extends ApplicationHelper {

  private $record = NULL;
  private $currentForm = '';

  public function begin(ActiveRecord $record) {
    $this->record = $record;
    $this->currentForm = strtolower(get_class($record));
    return '<form action="' . $this->getLink(array()) . '" method="post">';
  }

  public function fieldName($field) {
    return $this->currentForm . '[' . $field . ']';
  }

  public function fieldId($field, $value = NULL) {
    $id = $this->currentForm . '_' . $field;
    if (isset($value)) {
      $id .= '_' . $value;
    }
    return $id;
  }

  public function label($label, $field = NULL, $options = array()) {
    $html = '<label for="' . $this->fieldId($field) . '"';
    if (isset($options['class'])) {
      $html .= ' class="' . $options['class'] . '"';
    }
    $html .= '>' . $label . '</label>';
    return $html;
  }

  public function field($field, $options) {
    $type = $this->record->getFieldType($field);
    switch ($type) {
      case 'text':
        return $this->textarea($field, $options);
      default:
        if (strpos($field, 'pass') !== FALSE) {
          return $this->password($field, $options);
        }
        return $this->text($field, $options);
    }
  }

  public function fieldValue($field) {
    if (isset($this->record->$field)) {
      return h($this->record->$field);
    }
    return '';
  }

  private function addAttributes($options) {
    $html = '';
    foreach ($options as $attribute => $value) {
      $html .= ' ' . $attribute . '="' . $value . '"';
    }
    return $html;
  }

  public function text($field, $options) {
    $html = '<input type="text" name="' . $this->fieldName($field) .'"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    if (!isset($options['class'])) {
      $options['class'] = 'text';
    }
    $html .= $this->addAttributes($options);
    if ($this->fieldValue($field) != '') {
      $html .= ' value="' . $this->fieldValue($field) . '"';
    }
    $html .= ' />';
    return $html;
  }

  public function radio($field, $value, $options) {
    $html = '<input type="radio" name="' . $this->fieldName($field) .'"';
    $html .= ' id="' . $this->fieldId($field, $value) . '"';
    $html .= ' value="' . $value . '"';
    $html .= $this->addAttributes($options);
    if ($this->fieldValue($field) == $value) {
      $html .= ' checked="checked"';
    }
    $html .= ' /> ';
    return $html;
  }

  public function password($field, $options) {
    $html = '<input type="password" name="' . $this->fieldName($field) .'"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    if (!isset($options['class'])) {
      $options['class'] = 'text';
    }
    $html .= $this->addAttributes($options);
    $html .= ' />';
    return $html;
  }

  public function textarea($field, $options) {
    $html = '<textarea name="' . $this->fieldName($field) .'"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    $html .= $this->addAttributes($options);
    $html .= '>';
    if ($this->fieldValue($field) != '') {
      $html .= $this->fieldValue($field);
    }
    $html .= '</textarea>';
    return $html;
  }

  public function submit($value, $field = 'submit', $options) {
    $html = '<input type="submit" name="' . $field . '"';
    $html .= ' id="' . $this->fieldId($field) . '"';
    $html .= ' value="' . $value . '"';
    if (!isset($options['class'])) {
      $options['class'] = 'button';
    }
    $html .= $this->addAttributes($options);
    $html .= ' /> ';
    return $html;
  }
    
  public function end() {
    return '</form>';
  }
}
