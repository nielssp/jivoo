<?php
/**
 * A content format used by an editor
 * @package Jivoo\Editors
 */
interface IContentFormat {

  public function getName();

  /**
   * Convert to pure HTML for database storage and page display
   * @param string $text Formatted text
   * @return string HTML text
   */
  public function toHtml($content);
}
