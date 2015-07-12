<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Content\FormatHelper;
/**
 * A single form field.
 */
class FormField implements IFormField {
  /**
   * @var IFormField Field.
   */
  private $field;
  
  /**
   * @var FormHelper Helper.
   */
  
  /**
   * Construct form field.
   * @param FormatHelper $helper Helper.
   * @param IFormField $field Field.
   */
  public function __construct(FormHelper $helper, IFormField $field) {
    $this->helper = $helper;
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
   * Whether or not the field contains errors.
   * @return boolean True if valid, false if errors.
   */
  public function isValid() {
    return $this->getError() === null;
  }
  
  /**
   * Opposite of {@see isValid()}.
   * @return boolean True if invalid, false otherwise.
   */
  public function isInvalid() {
    return $this->getError() !== null;
  }
  
  /**
   * Output an error message or a default string.
   * @param string $default Output if field is valid.
   * @return string Error or default string.
   */
  public function error($default = '') {
    $error = $this->getError();
    if ($error === null)
      return $default;
    return $error;
  }
  
  /**
   * Output a message if the field is valid.
   * @param string $output Output to return if field is valid.
   * @return string Returns output if field is valid, otherwise the empty string.
   */
  public function ifValid($output) {
    if ($this->isValid())
      return $output;
    return '';
  }
  
  /**
   * Output a message if the field is invalid.
   * @param string $output Output to return if field is invalid.
   * @return string Returns output if field is invalid, otherwise the empty string.
   */
  public function ifInvalid($output) {
    if ($this->isInvalid())
      return $output;
    return '';
  }
  
  /**
   * Output a message if the field is required.
   * @param string $output Output to return if field is required.
   * @return string Returns output if required is valid, otherwise the empty string.
   */
  public function ifRequired($output) {
    if ($this->isRequired())
      return $output;
    return '';
  }
  
  /**
   * Output a message if the field is optional.
   * @param string $output Output to return if field is optional.
   * @return string Returns output if field is optional, otherwise the empty string.
   */
  public function ifOptional($output) {
    if ($this->isOptional())
      return $output;
    return '';
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