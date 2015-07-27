<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Models\IBasicRecord;
use Jivoo\Models\Selection\IReadSelection;
use Jivoo\Models\DataType;
use Jivoo\Models\EnumDataType;

/**
 * A helper for creating HTML forms
 */
class FormHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Html'); 
  
  /**
   * @var string[] Element stack for option-elements and nested optgroups.
   */
  private $stack = array();

  /**
   * @var IBasicRecord Associated record.
   */
  private $record = null;
  
  /**
   * @var array Form data.
   */
  private $data = array();
  
  /**
   * @var \Jivoo\Models\IBasicModel Associated model.
   */
  private $model = null;
  
  /**
   * @var string Name of current form.
   */
  private $name = null;
  
  /**
   * @var string Id of current form.
   */
  private $id = null;
  
  /**
   * @var string[] Associative array of field names and error messages.
   */
  private $errors = array();
  
  /**
   * @var mixed Value of current select-element.
   */
  private $selectValue = null;
  
  private $startTag = null;

  /**
   * Begin a form. End it with {@see end()}.
   * 
   * Automatically creates a hidden access token if method is
   * "post" (default). The method can be changed with the $attributes-parameter,
   * if methods other than "post" and "get" are used, a hidden element named
   * "method" is created with the requested method.
   * 
   * @param array|ILinkable|string|null $route Form route, see {@see Routing}.
   * @param array $attributes Additional attributes for form, e.g. "method",
   * "id", "class", "name", etc. A special attribute "hiddenToken", can be
   * used to create the hidden access token field when the method is 'get'.
   * @throws FormHelperException If a form is already open.
   * @return string The HTML for the start of the form.
   */
  public function form($route = array(), $attributes = array()) {
    if (!empty($this->stack))
      throw new FormHelperException(tr('A form is already open.'));
    array_push($this->stack, 'form');
    $attributes = array_merge(array(
      'method' => 'post',
    ), $attributes);
    $specialMethod = null;
    if ($attributes['method'] != 'post' and
        $attributes['method'] != 'get') {
      $specialMethod = $attributes['method'];
      $attributes['method'] = 'post';
    }
    if (isset($attributes['id']))
      $this->id = $attributes['id'];
    if (isset($attributes['name']))
      $this->name = $attributes['name'];
    $hiddenToken = $attributes['method'] != 'get';
    if (isset($attributes['hiddenToken'])) {
      $hiddenToken = $attributes['hiddenToken'];
      unset($attributes['hiddenToken']);
    }
    $html = '<form action="' . $this->getLink($route) . '"';
    $html .= $this->addAttributes($attributes) . '>' . PHP_EOL;
    if ($hiddenToken)
      $html .= $this->hiddenToken();
    if (isset($specialMethod)) {
      $html .= $this->element('input', array(
        'type' => 'hidden',
        'name' => 'method',
        'value' => $specialMethod
      ));
    }
    if ($attributes['method'] == 'post')
      $this->data = $this->request->data;
    else
      $this->data = $this->request->query;
    if (isset($this->name)) {
      if (isset($this->data[$this->name]))
        $this->data = $this->data[$this->name];
      else
        $this->data = array();
    }
    $this->startTag = $html;
    return $html;
  }
  
  /**
   * Get the opening tag of the current form.
   * @return string The HTML for the start of the form.
   */
  public function begin() {
    return $this->startTag;
  }

  /**
   * Begin a form for a record. End it with {@see end()}.
   * @param IBasicRecord $record A record.
   * @param array|ILinkable|string|null $route Form route, see {@see Routing}.
   * @param array $attributes Additional attributes for form, see {@see form()}.
   * used to create the hidden access token field when the method is 'get'.
   * @return string The HTML for the start of the form.
   */
  public function formFor(IBasicRecord $record, $route = array(), $attributes = array()) {
    $this->record = $record;
    $this->model = $record->getModel();
    $attributes = array_merge(array(
      'id' => $this->model->getName(),
      'name' => $this->model->getName(),
    ), $attributes);
    $this->errors = $this->record->getErrors();
    return $this->form($route, $attributes);
  }

  /**
   * End the current element, e.g. a form opened with {@see form()}.
   * @throws FormHelperException If no form or element is open.
   * @return string The HTML for the end of the form or element.
   */
  public function end() {
    if (empty($this->stack))
      throw new FormHelperException(tr('No form or form element is open.'));
    $element = array_pop($this->stack);
    switch ($element) {
      case 'form':
        $this->errors = array();
        $this->record = null;
        $this->model = null;
        $this->name = null;
        $this->id = null;
        return '</form>' . PHP_EOL;
      case 'select':
        $this->selectValue = null;
        return '</select>' . PHP_EOL;
      case 'optgroup':
        return '</optgroup>' . PHP_EOL;
    }
  }
  
  /**
   * Whether or not a form is open.
   * @return boolean True if open.
   */
  public function isOpen() {
    return !empty($this->stack);
  }
  
  /**
   * Create a hidden token.
   * @see Request::createHiddenToken()
   * @return string HTML for a hidden element containing an access token.
   */
  public function hiddenToken() {
    return $this->request->createHiddenToken() . PHP_EOL;
  }
  
  /**
   * Get associated model if started with {@see formFor}.
   * @return IBasicModel Model.
   */
  public function getModel() {
    return $this->model;
  }

  /**
   * Get associated record if started with {@see formFor}.
   * @return IBasicRecord Record.
   */
  public function getRecord() {
    return $this->record;
  }
  
  /**
   * Get the id of a field. If the form has an id, that id is prepended along
   * with an underscore.
   * @param string|IFormExtension $field Field name.
   * @param string $value Value if checkbox/radio, will be appended along with
   * an underscore.
   * @return string Id.
   */
  public function id($field, $value = null) {
    if ($field instanceof IFormExtension)
      $field = $field->getName();
    if (strpos($field, '.') !== false)
      $field = str_replace('.', '_', $field);
    if (isset($this->id))
      $field = $this->id . '_' . $field;
    if (isset($value))
      $field .= '_' . $value;
    return $field;
  }
  
  /**
   * Get the name of a field. If the form has a name, that name is used in
   * combination with the field name, e.g.: "formName[fieldName]".
   * @param string|IFormExtension $field Field name.
   * @param string $value Value if checkbox/radio, will be appended along with
   * an underscore.
   * @return string Name.
   */
  public function name($field, $value = null) {
    if ($field instanceof IFormExtension)
      $field = $field->getName();
    $elements = array();
    if (strpos($field, '.') !== false) {
      $elements = explode('.', $field);
      $field = array_shift($elements);
    }
    if (isset($this->name)) {
      array_unshift($elements, $field);
      $field = $this->name;
    }
    if (isset($value) and !is_bool($value))
      $elements[] = $value;
    foreach ($elements as $element)
      $field .= '[' . $element . ']';
    return $field;
  }
  
  /**
   * Get value of field, e.g. if form was submitted, or associated recod
   * contains data.
   * @param string|IFormExtension $field Field name.
   * @return mixed Value of field or null if undefined.
   */
  public function value($field) {
    if ($field instanceof IFormExtension)
      return $field->getValue($this->record);
    else if (isset($this->record)) {
      if (strpos($field, '.') !== false) {
        $elements = explode('.', $field);
        $value = $this->record;
        foreach ($elements as $element)
          $value = $value[$element];
        return $value;
      }
      return $this->record->$field;
    }
    else if (isset($this->data[$field])) {
      if (strpos($field, '.') !== false) {
        $elements = explode('.', $field);
        $value = $this->data;
        foreach ($elements as $element)
          $value = $value[$element];
        return $value;
      }
      return $this->data[$field];
    }
    return null;
  }
  
  /**
   * Whether or not field is required.
   * @param string|IFormExtension $field Field name.
   * @return boolean True if required, false if optional.
   */
  public function isRequired($field) {
    if ($field instanceof IFormExtension)
      return $field->isRequired();
    if (isset($this->model) and $this->model->hasField($field))
      return $this->model->isRequired($field);
    return false;
  }
  
  /**
   * Whether or not field is optional.
   * @param string|IFormExtension $field Field name.
   * @return boolean True if optional, false if required.
   */
  public function isOptional($field) {
    return !$this->isRequired($field);
  }
  
  /**
   * Whether or not the form or field contains errors.
   * @param string|IFormExtension $field Field name, or null for entire form.
   * @return boolean True if valid, false if errors.
   */
  public function isValid($field = null) {
    if ($field instanceof IFormExtension)
      return $field->getError() === null;
    if (isset($field))
      return !isset($this->errors[$field]);
    return count($this->errors) == 0;
  }
  
  /**
   * Opposite of {@see isValid()}.
   * @param string|IFormExtension $field Field name.
   * @return boolean True if invalid, false otherwise.
   */
  public function isInvalid($field = null) {
    return !$this->isValid($field);
  }
  
  /**
   * Get all errors.
   * @return string[] Associative array mapping field names to error messages.
   */
  public function getErrors() {
    return $this->errors;
  }
  
  /**
   * Output an error message or a default string.
   * @param string|IFormExtension $field Field name.
   * @param string $default Output if field is valid.
   * @return string Error or default string.
   */
  public function error($field, $default = '') {
    if ($field instanceof IFormExtension) {
      $error = $field->getError();
      if (isset($error))
        return $error;
    }
    else if (isset($this->errors[$field])) {
      return $this->errors[$field];
    }
    return $default;
  }
  
  /**
   * Output a message if the field is valid.
   * @param string|IFormExtension $field Field name.
   * @param string $output Output to return if field is valid.
   * @return string Returns output if field is valid, otherwise the empty string.
   */
  public function ifValid($field, $output) {
    if ($this->isValid($field))
      return $output;
    return '';
  }

  /**
   * Output a message if the field is invalid.
   * @param string|IFormExtension $field Field name.
   * @param string $output Output to return if field is invalid.
   * @return string Returns output if field is invalid, otherwise the empty string.
   */
  public function ifInvalid($field, $output) {
    if ($this->isInvalid($field))
      return $output;
    return '';
  }

  /**
   * Output a message if the field is required.
   * @param string|IFormExtension $field Field name.
   * @param string $output Output to return if field is required.
   * @return string Returns output if required is valid, otherwise the empty string.
   */
  public function ifRequired($field, $output) {
    if ($this->isRequired($field))
      return $output;
    return '';
  }

  /**
   * Output a message if the field is optional.
   * @param string|IFormExtension $field Field name.
   * @param string $output Output to return if field is optional.
   * @return string Returns output if field is optional, otherwise the empty string.
   */
  public function ifOptional($field, $output) {
    if ($this->isOptional($field))
      return $output;
    return '';
  }
  
  /**
   * Output a labelled field with an optional description.
   * @param string|IFormExtension $field Field name.
   * @param string $type Field type, can be any of 'text', 'password', 'date',
   * 'time', 'dateTime', or 'file'.
   * @param string $label Label, default is to look up the label in the model.
   * @param string $description Optional description;
   * @param array $attributes Addtional element attributes for the field.
   * @return string HTML code.
   */
  public function standardField($field, $type = 'field', $label = null, $description = '', $attributes = array()) {
    if ($this->isRequired($field))
      $divAttributes = array('class' => 'field field-required');
    else
      $divAttributes = array('class' => 'field');
    $description = $this->error($field, $description);
    return $this->element(
      'div',
      $divAttributes,
      $this->label($field, $label) . PHP_EOL
        . $this->$type($field, $attributes) . PHP_EOL
        . $description
    );
  }

  /**
   * Output a label element.
   * @param string|IFormExtension $field Field name.
   * @param string $label Label, default is to look up the label in the model.
   * @param array $attributes Addtional element attributes.
   * @return string HTML label element.
   */
  public function label($field, $label = null, $attributes = array()) {
    if (!isset($label) ) {
      $label = '';
      if ($field instanceof IFormExtension)
        $label = $field->getLabel();
      else if (isset($this->model))
        $label = $this->model->getLabel($field);
    }
    $attributes = array_merge(array(
      'for' => $this->id($field)
    ), $attributes);
    return $this->element('label', $attributes, $label);
  }

  /**
   * Output a label element for a radio field.
   * @param string|IFormExtension $field Field name.
   * @param mixed $value Field value.
   * @param string $label Label.
   * @param array $attributes Additional element attributes.
   * @return string HTML label element.
   */
  public function radioLabel($field, $value, $label, $attributes = array()) {
    $attributes = array_merge(array(
      'for' => $this->id($field, $value)
    ), $attributes);
    return $this->element('label', $attributes, $label);
  }

  /**
   * Output a label element for a checkbox field.
   * @param string|IFormExtension $field Field name.
   * @param mixed $value Field value.
   * @param string $label Label.
   * @param array $attributes Additional element attributes.
   * @return string HTML label element.
   */
  public function checkboxLabel($field, $value, $label, $attributes = array()) {
    return $this->radioLabel($field, $value, $label, $attributes);
  }
  
  public function field($field, $attributes = array()) {
    $div = $this->Html->create('div', $attributes);
    $div->addClass('field');
    if ($this->isInvalid($field))
      $div->addClass('field-error');
    if ($this->isRequired($field))
      $div->addClass('field-required');
    $div->append($this->label($field));
    $div->append($this->input($field));
    $helpDiv = $this->Html->create('div', 'class=help');
    if ($this->isInvalid($field)) {
      $helpDiv->html($this->error($field));
      $div->append($helpDiv->toString());
    }
    else if ($div->hasProp('description')) {
      $helpDiv->html($div['description']);
      $div->append($helpDiv->toString());
    }
    return $div->toString();
  }
  
  /**
   * Output an input element. The type of the element is based on the field
   * type.
   * @param string|IFormExtension $field Field name.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function input($field, $attributes = array()) {
    if ($field instanceof IFormExtension) {
      $attributes = array_merge(array(
        'name' => $this->name($field),
        'id' => $this->id($field),
        'value' => $this->value($field)
      ), $attributes);
      return $field->getField($attributes);
    }
    if (isset($this->model) and $this->model->hasField($field)) {
      $type = $this->model->getType($field);
      if (isset($type)) {
        switch ($type->type) {
          case DataType::TEXT:
          case DataType::BINARY:
          case DataType::OBJECT:
            return $this->textarea($field, $attributes);
          case DataType::DATE:
            return $this->date($field, $attributes);
          case DataType::DATETIME:
            return $this->datetime($field, $attributes);
          case DataType::BOOLEAN:
            return $this->checkboxAndLabel($field, true, $attributes);
          case DataType::ENUM:
            return $this->selectOf($field, null, $attributes);
        }
      }
    }
    if (strpos(strtolower($field), 'password') !== false)
      return $this->password($field, $attributes);
    return $this->text($field, $attributes);
  }

  /**
   * Output an input element for a text input.
   * @param string|IFormExtension $field Field name.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function text($field, $attributes = array()) {
    return $this->inputElement('text', $field, $attributes);
  }

  /**
   * Output an input element for a date input.
   * @param string|IFormExtension $field Field name.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function date($field, $attributes = array()) {
    $attributes = array_merge(array(
      'name' => $this->name($field) . '[date]',
    ), $attributes);
    return $this->inputElement('date', $field, $attributes);
  }

  /**
   * Output an input element for a time input.
   * @param string|IFormExtension $field Field name.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function time($field, $attributes = array()) {
    $attributes = array_merge(array(
      'name' => $this->name($field) . '[time]',
    ), $attributes);
    return $this->inputElement('time', $field, $attributes);
  }

  /**
   * Output an input element for a datet/time input.
   * @param string|IFormExtension $field Field name.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function datetime($field, $attributes = array()) {
    return $this->inputElement('datetime', $field, $attributes);
  }

  /**
   * Output an input element for a password input.
   * @param string|IFormExtension $field Field name.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function password($field, $attributes = array()) {
    return $this->inputElement('password', $field, $attributes);
  }

  /**
   * Output an input element for a file input.
   * @param string|IFormExtension $field Field name.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function file($field, $attributes = array()) {
    return $this->inputElement('file', $field, $attributes);
  }

  /**
   * Output an input element for a hidden input.
   * @param string|IFormExtension $field Field name.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function hidden($field, $attributes = array()) {
    return $this->inputElement('hidden', $field, $attributes);
  }

  /**
   * Output an input element for a radio input.
   * @param string|IFormExtension $field Field name.
   * @param mixed $value Field value.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function radio($field, $value, $attributes = array()) {
    $attributes = array_merge(array(
      'type' => 'radio',
      'name' => $this->name($field)
    ), $attributes);
    return $this->checkbox($field, $value, $attributes);
  }

  /**
   * Output an input element for a checkbox input.
   * @param string|IFormExtension $field Field name.
   * @param mixed $value Field value.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function checkbox($field, $value, $attributes = array()) {
    $attributes = array_merge(array(
      'type' => 'checkbox',
      'name' => $this->name($field, $value),
      'value' => $value,
      'id' => $this->id($field, $value)
    ), $attributes);
    $attributes['value'] = $value;
    $currentValue = $this->value($field);
    if (is_bool($value)) {
      if ($currentValue)
        $attributes['checked'] = 'checked';
    }
    else if (is_array($currentValue) and (isset($currentValue[$value])
      or array_search($value, $currentValue) !== false)) {
      $attributes['checked'] = 'checked';
    }
    else if ($currentValue === $value) {
      $attributes['checked'] = 'checked';
    }
    return $this->element('input', $attributes);
  }
  
  /**
   * Output an input element for a checkbox input followed by a label.
   * @param string|IFormExtension $field Field name.
   * @param mixed $value Field value.
   * @param string $label Checkbox label.
   * @param array $attributes Additional element attributes.
   * @return string HTML input element.
   */
  public function checkboxAndLabel($field, $value, $label = null, $attributes = array()) {
    if (!isset($label))
      $label = $value;
    return $this->checkbox($field, $value, $attributes)
      . $this->checkboxLabel($field, $value, $label);
  }
  
  /**
   * Begin a select element. End with {@see end()}.
   * @param string|IFormExtension $field Field name.
   * @param array $attributes Additional element attributes. 
   * @throws FormHelperException If inappropriate location of element.
   * @return string Start of an HTML select element.
   */
  public function select($field, $attributes = array()) {
    if (end($this->stack) != 'form')
      throw new FormHelperException('Must be in a form before using select.');
    $attributes = array_merge(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field),
      'size' => 1
    ), $attributes);
    $value = $attributes['value'];
    unset($attributes['value']);
    $html = '<select' . $this->addAttributes($attributes) . '>' . PHP_EOL;
    array_push($this->stack, 'select');
    $this->selectValue = $value;
    return $html;
  }
  
  /**
   * Begin an optgroup element. End with {@see end()}.
   * @param string $label Group label.
   * @param array $attributes Additional element attributes.
   * @throws FormHelperException If inappropriate location of element.
   * @return string Start of an HTML optgroup element.
   */
  public function optgroup($label, $attributes = array()) {
    if (end($this->stack) != 'select')
      throw new FormHelperException('Must be in a select-field before using optgroup.');
    $attributes['label'] = $label;
    array_push($this->stack, 'optgroup');
    return '<optgroup' . $this->addAttributes($attributes) . '>' . PHP_EOL;
  }

  /**
   * Output a select element consisting of a number of options.
   * @param string|IFormExtension $field Field name.
   * @param string[]|null $value Associative array of values and labels, or null
   * in which case the field type must be an enum.
   * @param array $attributes Additional element attributes.
   * @return string HTML select element.
   * @throws FormHelperException If unexpected field type.
   */
  public function selectOf($field, $options = null, $attributes = array()) {
    $attributes = array_merge(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field),
      'size' => 1
    ), $attributes);
    $currentValue = $attributes['value'];
    unset($attributes['value']);
    $html = '<select' . $this->addAttributes($attributes) . '>' . PHP_EOL;
    if (!is_array($options)) {
      $type = $this->model->getType($field);
      if (!$type->isEnum())
        throw new FormHelperException('Field must be of type enum.');
      $options = array_combine($type->values, $type->values);
    }
    foreach ($options as $value => $text) {
      $html .= '<option value="' . h($value) . '"';
      if ($currentValue == $value)
        $html .= ' selected="selected"';
      $html .= '>' . h($text) . '</option>' . PHP_EOL;
    }
    $html .= '</select>';
    return $html;
  }
  
  /**
   * Output a select element with options made from a selection.
   * @param string|IFormExtension $field Field name.
   * @param IReadSelection $selection Selection of records.
   * @param string $valueField Field to use for values.
   * @param string $labelField Field to use for labels.
   * @param array $attributes Additional element attributes.
   * @return string HTML select element.
   */
  public function selectFromSelection($field, IReadSelection $selection, $valueField, $labelField, $attributes = array()) {
    $attributes = array_merge(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field),
      'size' => 1
    ), $attributes);
    $currentValue = $attributes['value'];
    unset($attributes['value']);
    $html = '<select' . $this->addAttributes($attributes) . '>' . PHP_EOL;
    foreach ($selection as $record) {
      $value = $record->$valueField;
      $label = $record->$labelField;
      $html .= '<option value="' . h($value) . '"';
      if ($currentValue == $value)
        $html .= ' selected="selected"';
      $html .= '>' . h($label) . '</option>' . PHP_EOL;
    }
    $html .= '</select>';
    return $html;
  }
  
  /**
   * Output an option element.
   * @param string $value Option value.
   * @param string $text Option label.
   * @param array $attributes Additional element attributes.
   * @return string HTML option element.
   */
  public function option($value, $text, $attributes = array()) {
    $attributes['value'] = $value;
    if ($value == $this->selectValue)
      $attributes['selected'] = 'selected';
    return $this->element('option', $attributes, $text);
  }
  
  /**
   * Output a textarea element.
   * @param string|IFormExtension $field Field name.
   * @param array $attributes Additional element attributes.
   * @return string HTML textarea element.
   */
  public function textarea($field, $attributes = array()) {
    $attributes = array_merge(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field),
      'data-error' => $this->error($field, null),
    ), $attributes);
    $content = '';
    if (isset($attributes['value'])) {
      $content = $attributes['value'];
      unset($attributes['value']);
    }
    if (isset($attributes['size'])) {
      $size = explode('x', $attributes['size']);
      unset($attributes['size']);
      if (count($size) == 2) {
        $attributes['cols'] = $size[0]; 
        $attributes['rows'] = $size[1];
      }
    }
    return $this->element('textarea', $attributes, $content);
  }

  /**
   * Output a submit button.
   * @param string $label Button label.
   * @param array $attributes Additional element attributes.
   * @return string HTML submit element.
   */
  public function submit($label, $attributes = array()) {
    $attributes = array_merge(array(
      'type' => 'submit',
      'value' => $label
    ), $attributes);
    return $this->element('input', $attributes);
  }
  
  /**
   * Output a form containing only a submit button.
   * @param string $label Button label.
   * @param array|ILinkable|string|null $route Form route, see {@see Routing}.
   * @param array $attributes Additional element attributes for button.
   * @return string HTML form element.
   */
  public function actionButton($label, $route = array(), $attributes = array()) {
    return $this->form($route)
      . $this->submit($label, $attributes)
      . $this->end();
  }
  
  /**
   * Create an input element.
   * @param string $type Type.
   * @param string $field Field name.
   * @param array $attributes Element attributes.
   * @return string HTML element.
   */
  private function inputElement($type, $field, $attributes) {
    $attributes = array_merge(array(
      'type' => $type,
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $type != 'password' ? $this->value($field) : null,
      'data-error' => $this->error($field, null),
    ), $attributes);
    return $this->element('input', $attributes);
  }
  
  /**
   * Create an HTML element.
   * @param string $tag HTML tag.
   * @param array $attributes HTML attributes.
   * @param string $content Content.
   * @return string HTML element.
   */
  private function element($tag, $attributes, $content = null) {
    if (isset($content))
      return '<' . $tag . $this->addAttributes($attributes) . '>' . $content . '</' . $tag . '>' . PHP_EOL;
    return '<' . $tag . $this->addAttributes($attributes) . ' />' . PHP_EOL;
  }

  /**
   * Add additional attributes.
   * @param array $options An associative array of additional attributes to add
   * to field.
   * @return string HTML attributes.
   */
  private function addAttributes($attributes) {
    $html = '';
    foreach ($attributes as $attribute => $value) {
      if (is_scalar($value))
        $html .= ' ' . $attribute . '="' . h($value) . '"';
    }
    return $html;
  }
}
