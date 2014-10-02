<?php
/**
 * Format used by {@see HtmlEditor}
 * @package Jivoo\Editors
 */
class HtmlFormat implements IContentFormat {
  public function toHtml($text) {
    return html_entity_decode($text, null, 'UTF-8');
  }
  
  public function toText($content) {
    $encoder = new HtmlEncoder();
    return $encoder->encode($content);
  }
}
