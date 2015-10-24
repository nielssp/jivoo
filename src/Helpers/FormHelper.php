<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Models\BasicRecord;
use Jivoo\Models\Selection\ReadSelection;
use Jivoo\Models\DataType;
use Jivoo\Models\EnumDataType;
use Jivoo\Helpers\Form\Field;
use Jivoo\Helpers\Form\FormMacros;

/**
 * A helper for creating HTML forms
 */
class FormHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Html'); 

  /**
   * @var BasicRecord Associated record.
   */
  private $record = null;
  
  /**
   * @var array Form data.
   */
  private $data = array();
  
  /**
   * @var \Jivoo\Models\BasicModel Associated model.
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
  
  protected function init() {
    if (isset($this->view) and isset($this->view->compiler))
      $this->view->compiler->addMacros(new FormMacros());
  }

  /**
   * Begin a form. End it with {@see end()}.
   * 
   * Automatically creates a hidden access token if method is
   * "post" (default). The method can be changed with the $attributes-parameter,
   * if methods other than "post" and "get" are used, a hidden element named
   * "method" is created with the requested method.
   * 
   * @param array|Linkable|string|null $route Form route, see {@see Routing}.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}. A special attribute "hiddenToken", can be
   * used to create the hidden access token field when the method is 'get'.
   */
  public function form($route = array(), $attributes = array()) {
    $form = $this->Html->begin('form', 'method=post');
    $form->attr('action', $this->getLink($route));
    $form->attr($attributes);
    
    $specialMethod = null;
    if ($form['method'] != 'post' and
        $form['method'] != 'get') {
      $specialMethod = $form['method'];
      $form['method'] = 'post';
    }
    
    if (isset($form['id']))
      $this->id = $form['id'];
    if (isset($form['name']))
      $this->name = $form['name'];
    
    $hiddenToken = $form['method'] != 'get';
    if ($form->hasProp('hiddenToken'))
      $hiddenToken = $form['hiddenToken'];
    
    if ($hiddenToken)
      $form->append($this->hiddenToken());
    
    if (isset($specialMethod)) {
      $form->append($this->Html->create('input', array(
        'type=hidden name=method', 'value' => $specialMethod
      )));
    }
    if ($form['method'] == 'post')
      $this->data = $this->request->data;
    else
      $this->data = $this->request->query;

    if (isset($this->name)) {
      if (isset($this->data[$this->name]))
        $this->data = $this->data[$this->name];
      else
        $this->data = array();
    }
  }

  /**
   * Begin a form for a record. End it with {@see end()}.
   * @param BasicRecord $record A record.
   * @param array|Linkable|string|null $route Form route, see {@see Routing}.
   * @param array $attributes Additional attributes for form, see {@see form()}.
   * used to create the hidden access token field when the method is 'get'.
   */
  public function formFor(BasicRecord $record, $route = array(), $attributes = array()) {
    $this->record = $record;
    $this->model = $record->getModel();
    $attributes = Html::mergeAttributes(array(
      'id' => $this->model->getName(),
      'name' => $this->model->getName(),
    ), $attributes);
    $this->errors = $this->record->getErrors();
    $this->form($route, $attributes);
  }

  /**
   * Get the current form element, e.g. a form opened with {@see form()}.
   * @throws FormHelperException If no form or element is open.
   * @return Html The HTML element for the form element.
   */
  public function peek() {
    $form = $this->Html->peek();
    if (!isset($form))
      throw new FormHelperException(tr('No form or form element is open.'));
    if ($form instanceof Field)
      return $form;
    if (array_search($form->tag, array('form', 'select', 'optgroup')) === false)
      throw new FormHelperException(tr('No form or form element is open.'));
    return $form;
  }

  /**
   * End the current element, e.g. a form opened with {@see form()}.
   * @throws FormHelperException If no form or element is open.
   * @return Html The HTML element for the end of the form or element.
   */
  public function end() {
    $form = $this->Html->end();
    if (!isset($form))
      return null;
    if ($form instanceof Field)
      return $form;
    switch ($form->tag) {
      case 'form':
        $this->errors = array();
        $this->record = null;
        $this->model = null;
        $this->name = null;
        $this->id = null;
        break;
      case 'select':
      case 'optgroup':
        break;
      default:
        throw new FormHelperException(tr('Top element "%1" is not a form element.', $form->tag));
    }
    return $form;
  }
  
  /**
   * Whether or not a form is open.
   * @return boolean True if open.
   */
  public function isOpen() {
    $form = $this->Html->peek();
    return isset($form) and ($form instanceof Field or
      array_search($form->tag, array('form', 'select', 'optgroup')) !== false);
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
   * @return BasicModel Model.
   */
  public function getModel() {
    return $this->model;
  }

  /**
   * Get associated record if started with {@see formFor}.
   * @return BasicRecord Record.
   */
  public function getRecord() {
    return $this->record;
  }
  
  /**
   * Get the id of a field. If the form has an id, that id is prepended along
   * with an underscore.
   * @param string|FormExtension $field Field name.
   * @param string $value Value if checkbox/radio, will be appended along with
   * an underscore.
   * @return string Id.
   */
  public function id($field, $value = null) {
    if ($field instanceof FormExtension)
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
   * @param string|FormExtension $field Field name.
   * @param string $value Value if checkbox/radio, will be appended along with
   * an underscore.
   * @return string Name.
   */
  public function name($field, $value = null) {
    if ($field instanceof FormExtension)
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
   * @param string|FormExtension $field Field name.
   * @return mixed Value of field or null if undefined.
   */
  public function value($field) {
    if ($field instanceof FormExtension)
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
   * @param string|FormExtension $field Field name.
   * @return boolean True if required, false if optional.
   */
  public function isRequired($field) {
    if ($field instanceof FormExtension)
      return $field->isRequired();
    if (isset($this->model) and $this->model->hasField($field))
      return $this->model->isRequired($field);
    return false;
  }
  
  /**
   * Whether or not field is optional.
   * @param string|FormExtension $field Field name.
   * @return boolean True if optional, false if required.
   */
  public function isOptional($field) {
    return !$this->isRequired($field);
  }
  
  /**
   * Whether or not the form or field contains errors.
   * @param string|FormExtension $field Field name, or null for entire form.
   * @return boolean True if valid, false if errors.
   */
  public function isValid($field = null) {
    if ($field instanceof FormExtension)
      return $field->getError() === null;
    if (isset($field))
      return !isset($this->errors[$field]);
    return count($this->errors) == 0;
  }
  
  /**
   * Opposite of {@see isValid()}.
   * @param string|FormExtension $field Field name.
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
   * @param string|FormExtension $field Field name.
   * @param string $default Output if field is valid.
   * @return string Error or default string.
   */
  public function error($field, $default = '') {
    if ($field instanceof FormExtension) {
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
   * @param string|FormExtension $field Field name.
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
   * @param string|FormExtension $field Field name.
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
   * @param string|FormExtension $field Field name.
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
   * @param string|FormExtension $field Field name.
   * @param string $output Output to return if field is optional.
   * @return string Returns output if field is optional, otherwise the empty string.
   */
  public function ifOptional($field, $output) {
    if ($this->isOptional($field))
      return $output;
    return '';
  }

  /**
   * Output a label element.
   * @param string|FormExtension $field Field name.
   * @param string $label Label, default is to look up the label in the model.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML label element.
   */
  public function label($field, $label = null, $attributes = array()) {
    if (!isset($label) ) {
      $label = '';
      if ($field instanceof FormExtension)
        $label = $field->getLabel();
      else if (isset($this->model))
        $label = $this->model->getLabel($field);
    }
    $elem = $this->Html->create('label');
    $elem->attr('for', $this->id($field));
    $elem->attr($attributes);
    $elem->html($label);
    return $elem->toString();
  }

  /**
   * Output a label element for a radio field.
   * @param string|FormExtension $field Field name.
   * @param mixed $value Field value.
   * @param string $label Label..
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML label element.
   */
  public function radioLabel($field, $value, $label, $attributes = array()) {
    $elem = $this->Html->create('label');
    $elem->attr('for', $this->id($field, $value));
    $elem->attr($attributes);
    $elem->html($label);
    return $elem->toString();
  }

  /**
   * Output a label element for a checkbox field.
   * @param string|FormExtension $field Field name.
   * @param mixed $value Field value.
   * @param string $label Label..
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML label element.
   */
  public function checkboxLabel($field, $value, $label, $attributes = array()) {
    return $this->radioLabel($field, $value, $label, $attributes);
  }
  
  /**
   * Output a form field (div with label, input element and optional help text
   * (or error message)).
   * 
   * Special attributes:
   *  * label: A label (string), attributes for label element (array), or null
   *      for no label.
   *  * input: Attributes for input-element, see {@see input}.
   *  * help: Attributes for help-element.
   *  * description: Description string.
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return strinf Form field HTML.
   */
  public function field($field, $attributes = array()) {
    $div = new Field($this, $field);
    $this->Html->begin($div);
    $div->attr($attributes);
    return $div;
  }
  
  /**
   * Output an input element. The type of the element is based on the field
   * type.
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Input attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML input element.
   */
  public function input($field, $attributes = array()) {
    if ($field instanceof FormExtension) {
      $attributes = Html::mergeAttributes(array(
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
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML input element.
   */
  public function text($field, $attributes = array()) {
    return $this->inputElement('text', $field, $attributes);
  }

  /**
   * Output an input element for a date input.
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML input element.
   */
  public function date($field, $attributes = array()) {
    $attributes = Html::mergeAttributes(array(
      'name' => $this->name($field) . '[date]',
    ), $attributes);
    return $this->inputElement('date', $field, $attributes);
  }

  /**
   * Output an input element for a time input.
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML input element.
   */
  public function time($field, $attributes = array()) {
    $attributes = Html::mergeAttributes(array(
      'name' => $this->name($field) . '[time]',
    ), $attributes);
    return $this->inputElement('time', $field, $attributes);
  }

  /**
   * Output an input element for a datet/time input.
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML input element.
   */
  public function datetime($field, $attributes = array()) {
    return $this->inputElement('datetime', $field, $attributes);
  }

  /**
   * Output an input element for a password input.
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML input element.
   */
  public function password($field, $attributes = array()) {
    return $this->inputElement('password', $field, $attributes);
  }

  /**
   * Output an input element for a file input.
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML input element.
   */
  public function file($field, $attributes = array()) {
    return $this->inputElement('file', $field, $attributes);
  }

  /**
   * Output an input element for a hidden input.
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML input element.
   */
  public function hidden($field, $attributes = array()) {
    return $this->inputElement('hidden', $field, $attributes);
  }

  /**
   * Output an input element for a radio input.
   * @param string|FormExtension $field Field name.
   * @param mixed $value Field value.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML input element.
   */
  public function radio($field, $value, $attributes = array()) {
    $attributes = Html::mergeAttributes(array(
      'type' => 'radio',
      'name' => $this->name($field),
      'hidden' => false
    ), $attributes);
    return $this->checkbox($field, $value, $attributes);
  }

  /**
   * Output an input element for a checkbox input.
   * @param string|FormExtension $field Field name.
   * @param mixed $value Field value.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML input element.
   */
  public function checkbox($field, $value, $attributes = array()) {
    $elem = $this->Html->create('input', 'type=checkbox');
    $elem->attr(array(
      'type' => 'checkbox',
      'name' => $this->name($field, $value),
      'value' => strval($value),
      'id' => $this->id($field, $value),
      'hidden' => true
    ));
    $elem->attr($attributes);
    $currentValue = $this->value($field);
    $withHidden = $elem->prop('hidden');
    $hidden = '';
    if (is_bool($value)) {
      if ($currentValue)
        $elem->attr('checked', true);
      if ($withHidden) {
        $hidden = $this->Html->create('input', array(
          'type=hidden', 'name' => $this->name($field, $value), 'value' => false
        ))->toString();
      }
    }
    else if (is_array($currentValue) and (isset($currentValue[$value])
        or array_search($value, $currentValue) !== false)) {
      $elem->attr('checked', true);
    }
    else if ($currentValue === $value) {
      $elem->attr('checked', true);
    }
    return $hidden . $elem->toString();
  }
  
  /**
   * Output an input element for a checkbox input followed by a label.
   * @param string|FormExtension $field Field name.
   * @param mixed $value Field value.
   * @param string $label Checkbox label.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
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
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.. 
   * @throws FormHelperException If inappropriate location of element.
   */
  public function select($field, $attributes = array()) {
    $attributes = Html::mergeAttributes(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field),
      'size' => 1
    ), $attributes);
    $elem = $this->Html->begin('select', $attributes);
    $value = $elem->prop('value');
  }
  
  /**
   * Begin an optgroup element. End with {@see end()}.
   * @param string $label Group label.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @throws FormHelperException If inappropriate location of element.
   */
  public function optgroup($label, $attributes = array()) {
    $select = $this->peek();
    if ($select->tag != 'select')
      throw new FormHelperException('Must be in a select-field before using optgroup.');
    $elem = $this->Html->begin('optgroup', $attributes);
    $elem->prop('value', $select->prop('value'));
    $elem->attr('label', $label);
  }

  /**
   * Output a select element consisting of a number of options.
   * @param string|FormExtension $field Field name.
   * @param string[]|null $value Associative array of values and labels, or null
   * in which case the field type must be an enum.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML select element.
   * @throws FormHelperException If unexpected field type.
   */
  public function selectOf($field, $options = null, $attributes = array()) {
    $elem = $this->Html->create('select');
    $elem->attr(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field),
      'size' => 1
    ));
    $elem->attr($attributes);
    $currentValue = $elem->prop('value');
    if (!is_array($options)) {
      $type = $this->model->getType($field);
      if (!$type->isEnum())
        throw new FormHelperException('Field must be of type enum.');
      $options = array_combine($type->values, $type->values);
    }
    foreach ($options as $value => $text) {
      $optElem = $this->Html->create('option');
      $optElem->attr('value', $value);
      $optElem->html(h($text));
      if ($currentValue == $value)
        $optElem->attr('selected', true);
      $elem->append($optElem->toString());
    }
    return $elem->toString();
  }
  
  /**
   * Output a select element with options made from a selection.
   * @param string|FormExtension $field Field name.
   * @param ReadSelection $selection Selection of records.
   * @param string $valueField Field to use for values.
   * @param string $labelField Field to use for labels.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML select element.
   */
  public function selectFromSelection($field, ReadSelection $selection, $valueField, $labelField, $attributes = array()) {
    $elem = $this->Html->create('select');
    $elem->attr(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field),
      'size' => 1
    ));
    $elem->attr($attributes);
    $currentValue = $elem->prop('value');
    foreach ($selection as $record) {
      $value = $record->$valueField;
      $label = $record->$labelField;
      $optElem = $this->Html->create('option');
      $optElem->attr('value', $value);
      $optElem->html(h($label));
      if ($currentValue == $value)
        $optElem->attr('selected', true);
      $elem->append($optElem->toString());
    }
    return $elem->toString();
  }
  
  /**
   * Output an option element.
   * @param string $value Option value.
   * @param string $text Option label.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML option element.
   */
  public function option($value, $text, $attributes = array()) {
    $elem = $this->Html->create('option', $attributes);
    $elem->attr('value', $value);
    $select = $this->peek();
    if (($select->tag == 'select' or $select->tag == 'optgroup') and $value == $select->prop('value'))
      $elem->attr('selected', true);
    $elem->html(h($text));
    return $elem->toString();
  }
  
  /**
   * Output a textarea element.
   * @param string|FormExtension $field Field name.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML textarea element.
   */
  public function textarea($field, $attributes = array()) {
    $elem = $this->Html->create('textarea');
    $elem->attr(Html::mergeAttributes(array(
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $this->value($field),
      'data-error' => $this->error($field, null),
    ), $attributes));
    
    if ($elem->hasProp('value'))
      $elem->html(h($elem['value']));

    if ($elem->hasProp('size')) {
      $size = explode('x', $elem['size']);
      if (count($size) == 2) {
        $elem->attr('cols', $size[0]);
        $elem->attr('row', $size[1]);
      }
      else {
        $elem->attr('row', $size);
      }
    }
    
    return $elem->toString();
  }

  /**
   * Output a submit button.
   * @param string $label Button label.
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML submit element.
   */
  public function submit($label, $attributes = array()) {
    $button = $this->Html->create('input', 'type=submit');
    $button->attr('value', $label);
    $button->attr(Html::readAttributes($attributes));
    return $button->toString();
  }
  
  /**
   * Output a form containing only a submit button.
   * @param string $label Button label.
   * @param array|Linkable|string|null $route Form route, see {@see Routing}.
   * @param string|string[] $attributes Attributes for button, see
   * {@see Html::readAttributes}.
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
   * @param string|string[] $attributes Attributes, see
   * {@see Html::readAttributes}.
   * @return string HTML element.
   */
  private function inputElement($type, $field, $attributes) {
    $attributes = Html::mergeAttributes(array(
      'type' => $type,
      'name' => $this->name($field),
      'id' => $this->id($field),
      'value' => $type != 'password' ? $this->value($field) : null,
      'data-error' => $this->error($field, null),
    ), $attributes);
    return $this->Html->create('input', $attributes)->toString();
  }
}
