<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

/**
 * A single form field.
 */
class FormField implements IFormField {
  /**
   * @var IFormField Field.
   */
  private $field;
  
  /**
   * Construct form field.
   * @param IFormField $field Field.
   */
  public function __construct(IFormField $field) {
    $this->field = $field;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired() {
    return $this->field->isRequired();
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->field->getLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function getError() {
    return $this->field->getError();
  }
  
  /**
   * {@inheritdoc}
   */
  public function render($value = null, $name = null, $id = null) {
    return $this->field->render($value, $name, $id);
  }
  
  /**
   * Render field.
   * @return string HTML.
   */
  public function __toString() {
    return $this->render();
  }
}