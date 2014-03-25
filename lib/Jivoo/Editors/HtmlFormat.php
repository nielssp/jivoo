<?php
/**
 * Format used by {@see HtmlEditor}
 * @package Jivoo\Editors
 */
class HtmlFormat implements IContentFormat {
  public function toHtml($text) {
    return html_entity_decode($text, null, 'UTF-8');
  }

  public function fromHtml($html) {
    return $html;
  }
}
