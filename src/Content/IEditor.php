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
interface IEditor {
  /**
   * Get content format used by editor.
   * @return IContentFormat Format object.
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
