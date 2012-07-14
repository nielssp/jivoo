<?php
class TextFormat implements IContentFormat {
  
  public function toHtml($text) {
    $html = str_replace("\n\n", "</p><p>", $text);
    $html = str_replace("\n", "<br />\n", $html);
    /** @todo Improve URL-detection */
    $html = preg_replace('/ (https?:\/\/[\S]+) /i', ' <a href="\\1">\\1</a> ', $html);
    return '<p>' . $html . '</p>';
  }
  
  public function fromHtml($html) {
    $text = str_replace("\n", '', $html);
    $text = preg_replace('/<\/p><p>/i', "\n\n", $text);
    $text = preg_replace('/<br +\/?>/i', "\n", $text);
    return $text;
  }
}
