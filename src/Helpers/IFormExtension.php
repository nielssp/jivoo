<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Models\BasicRecord;
use Jivoo\View\ViewExtension;

/**
 * Use this interface to extend the functionality of some forms (see
 * {@see \Jivoo\View\ViewExtensions}.
 */
interface FormExtension extends ViewExtension {
  /**
   * Get name of field.
   * @return string Name.
   */
  public function getName();
  
  /**
   * Get value of field.
   * @param BasicRecord $record A record.
   * @return string Value. 
   */
  public function getValue(BasicRecord $record = null);
  
  /**
   * Get label for field.
   * @return string Label.
   */
  public function getLabel();
  
  /**
   * Get field HTML.
   * @param string[] $attributes Additional element attributes.
   * @return string HTML code.
   */
  public function getField($attributes = array());
  
  /**
   * Whether field is required.
   * @return bool True if required, false if optional.
   */
  public function isRequired();
  
  /**
   * Get error message for field if any.
   * @return string|null Error message or null if field is valid (or no data has
   * been submitted).
   */
  public function getError();
}