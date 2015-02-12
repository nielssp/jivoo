<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

/**
 * A content format.
 * @package Jivoo\Content
 */
interface IContentFormat {
  /**
   * Get identifying name of format. 
   * @return string Name.
   */
  public function getName();

  /**
   * Convert to pure HTML for database storage and page display.
   * @param string $content Content to convert.
   * @return string HTML text.
   */
  public function toHtml($content);
}
