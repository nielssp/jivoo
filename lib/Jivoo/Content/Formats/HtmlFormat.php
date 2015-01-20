<?php
/**
 * Html format.
 * @package Jivoo\Content\Formats
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
