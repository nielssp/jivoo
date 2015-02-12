<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\View\IViewExtension;

/**
 * A form extension, i.e. something that acts like a form field.
 * @package Jivoo\Helpers
 */
interface IFormExtension extends IViewExtension {
  /**
   * Output label element.
   * @param string $label Label.
   * @param array $attributes Additional attributes.
   * @return string HTML label element.
   */
  public function label($label = null, $attributes = array());
  
  /**
   * Output string if field required.
   * @param string $output Output if required.
   * @return string Output or empty string.
   */
  public function ifRequired($output);
  
  /**
   * Output field.
   * @param array $attributes Additional attributes.
   * @return string HTML element.
   */
  public function field($attributes = array());
  
  /**
   * Output error if invalid.
   * @param string $default Output if valid.
   * @return string Error message if invalid, otherwise default string. 
   */
  public function error($default = '');
}