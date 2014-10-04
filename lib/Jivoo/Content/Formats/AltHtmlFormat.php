<?php
/**
 * Format used by {@see HtmlEditor}
 * @package Jivoo\Editors
 */
class AltHtmlFormat implements IContentFormat {
  public function toHtml($text) {
    $html = preg_replace('/((\r\n|\n\r|\n|\r) *){2}/i', "</p><p>", $text);
    $html = preg_replace('/(\r\n|\n\r|\n|\r)/i', "<br />\n", $html);
    /** @todo Improve URL-detection */
    $html = preg_replace('/(https?:\/\/([^\n\r"< \Z()]+))/i',
      '<a href="\\1">\\2</a>', $html);
    if ($html == '') {
      return $html;
    }
    return '<p>' . $html . '</p>';
  }
  
  public function toText($content) {
    $encoder = new HtmlEncoder();
    return $encoder->encode($this->toHtml($content));
  }
}
