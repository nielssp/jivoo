<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Form;

use Jivoo\Helpers\Html;
use Jivoo\Helpers\FormHelper;

/**
 * A form field.
 */
class Field extends Html {
  /**
   * @var FormHelper
   */
  private $Form;
  
  /**
   * @var string Field name.
   */
  public $field;
  
  /**
   * @var bool
   */
  private $open = true;
  
  /**
   * Cosntruct form field.
   * @param FormHelper $Form Form helper.
   * @param string $field Field name.
   */
  public function __construct(FormHelper $Form, $field) {
    parent::__construct('div');
    $this->Form = $Form;
    $this->field = $field;
    $this->addClass('field');
    if ($Form->isInvalid($field))
      $this->addClass('field-error');
    if ($Form->isRequired($field))
      $this->addClass('field-required');
  }
  
  /**
   * Output a field label.
   * @param string $label Label text.
   * @param string|string[] $attributes Attributes, see
   * {@see \Jivoo\Helpers\Html::readAttributes}.
   * @return string Label html.
   */
  public function label($label = null, $attributes = array()) {
    $this->prop('label', '');
    return $this->Form->label($this->field, $label, $attributes);
  }
  
  /**
   * Close this field.
   * @return Field|null This field or null if already closed.
   */
  public function end() {
    if (!$this->open)
      return null;
    $this->open = false;
    $this->Form->end();
    if ($this->prop('defaultContent') !== 'none') {
      $label = null;
      if ($this->hasProp('label')) {
        $label = $this['label'];
        if (is_array($label))
          $this->prepend($this->label(null, $label));
        else if (!empty($label))
          $this->prepend($this->label($label));
      }
      else {
        $this->prepend($this->label());
      }
      if ($this->prop('input') !== '') {
        if ($this->hasProp('type')) {
          $type = $this['type'];
          $this->append($this->Form->$type($this->field, $this->prop('input')));
        }
        else {
          $this->append($this->Form->input($this->field, $this->prop('input')));
        }
      }
      $helpDiv = new Html('div');
      $helpDiv->addClass('help');
      if ($this->hasProp('help'))
        $helpDiv->attr($this['help']);
      if ($this->Form->isInvalid($this->field)) {
        $helpDiv->html($this->Form->error($this->field));
        $this->append($helpDiv->toString());
      }
      else if ($this->hasProp('description')) {
        $helpDiv->html($this['description']);
        $this->append($helpDiv->toString());
      }
    }
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function toString() {
    $this->end();
    return parent::toString();
  }
}