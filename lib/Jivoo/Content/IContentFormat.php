<?php
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
