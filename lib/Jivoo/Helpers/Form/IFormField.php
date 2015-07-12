<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

/**
 * Settings of a form field.
 */
interface IFormField {
  /**
   * Get label for field.
   * @return string Field label.
   */
  public function getLabel();

  /**
   * Whether or not field is required.
   * @return bool True if required.
   */
  public function isRequired();
  
  /**
   * Get error message for field.
   * @return string|null Error or null if no error.
   */
  public function getError();
  
  /**
   * Render the field.
   * @param mixed $value Field value or null if empty.
   * @param string $name Field name or null if empty.
   * @param string $id Field id or null if empty.
   * @return string HTML.
   */
  public function render($value = null, $name = null, $id = null);
}