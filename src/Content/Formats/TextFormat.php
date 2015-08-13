<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content\Formats;

use Jivoo\Content\ContentFormat;

/**
 * Plaintext format.
 */
class TextFormat implements ContentFormat {
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'text';
  }

  /**
   * {@inheritdoc}
   */
  public function toHtml($text) {
    return nl2br(h($text));
  }
}
