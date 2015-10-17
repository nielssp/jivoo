<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

use Jivoo\Helpers\FormHelper;

/**
 * An editor.
 */
interface Editor {
  /**
   * Convert content of editor to HTML.
   * @param string $content Editor content from form input.
   * @return string HTML.
   */
  public function toHtml($content);

  /**
   * Return name of format used by editor.
   * @return string Name.
   */
  public function getFormat();

  /**
   * Get HTML code for this editor.
   * @param FormHelper $Form A form helper.
   * @param string $field Name of field.
   * @param array $options Additional options.
   * @return string HTML.
   */
  public function field(FormHelper $Form, $field, $options = array());
}
