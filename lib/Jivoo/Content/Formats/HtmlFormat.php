<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content\Formats;

use Jivoo\Content\IContentFormat;

/**
 * Html format.
 */
class HtmlFormat implements IContentFormat {
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'html';
  }

  /**
   * {@inheritdoc}
   */
  public function toHtml($text) {
    return html_entity_decode($text, null, 'UTF-8');
  }
}
