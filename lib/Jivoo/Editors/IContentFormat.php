<?php
/**
 * A content format used by an editor
 * @package Jivoo\Editors
 */
interface IContentFormat {
  /**
   * Convert to pure HTML for database storage and page display
   * @param string $text Formatted text
   * @return string HTML text
   */
  public function toHtml($text);

  /**
   * Convert from HTML as stored in database
   * @param string $html HTML text from database
   * @return string Formatted text
   */
  public function fromHtml($html);
}
