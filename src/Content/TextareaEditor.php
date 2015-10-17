<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

use Jivoo\Helpers\FormHelper;

/**
 * A simple textarea-based editor for any format.
 */
class TextareaEditor implements Editor {
  /**
   * @var string Name of content format.
   */
  private $format;

  private $toHtml;
  
  /**
   * Construct textarea editor.
   * @param string $format Name of content format.
   * @param callable $toHtml Converts editor content to HTML, see {@see Editor::toHtml}.
   */
  public function __construct($format, $toHtml) {
    $this->format = $format;
    $this->toHtml = $toHtml;
  }

  /**
   * {@inheritdoc}
   */
  public function toHtml($content) {
    return call_user_func($this->toHtml, $content);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormat() {
    return $this->format;
  }

  /**
   * {@inheritdoc}
   */
  public function field(FormHelper $Form, $field, $options = array()) {
    return $Form->textarea($field, $options);
  }
}
