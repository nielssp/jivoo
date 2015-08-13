<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Models\IBasicRecord;
use Jivoo\View\IViewExtension;

/**
 * Use this interface to extend the functionality of some forms (see
 * {@see \Jivoo\View\ViewExtensions}.
 */
interface IFormExtension extends IViewExtension {
  /**
   * Get name of field.
   * @return string Name.
   */
  public function getName();
  
  /**
   * Get value of field.
   * @param IBasicRecord $record A record.
   * @return string Value. 
   */
  public function getValue(IBasicRecord $record = null);
  
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