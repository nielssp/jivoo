<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

/**
 * A content format used by an {@see Editor}.
 */
interface Format {
  /**
   * Convert to pure HTML for database storage and page display. Output does not
   * necessarily have to be sanitized or valid HTML.
   * @param string $content Content to convert.
   * @return string HTML text.
   */
  public function toHtml($content);
}
