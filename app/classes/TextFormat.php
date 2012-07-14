<?php
class TextFormat implements IContentFormat {
  
  public function toHtml($text) {
    $html = str_replace("\n", "<br />\n", $text);
    /** @todo Improve URL-detection */
    $html = preg_replace('/ (https?:\/\/[\S]+) /i', ' <a href="\\1">\\1</a> ', $html);
    return $html;
  }
  
  public function fromHtml($html) {
    $text = str_replace("\n", '', $html);
    $text = preg_replace('/<br +\/?>/i', "\n", $text);
    return $text;
  }
}
