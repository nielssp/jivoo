<?php
class TextFormat implements IContentFormat {

  public function toHtml($text) {
    $html = preg_replace('/((\r\n|\n\r|\n|\r) *){2}\n/i', "</p><p>", $text);
    $html = preg_replace('/(\r\n|\n\r|\n|\r)/i', "<br />\n", $html);
    /** @todo Improve URL-detection */
    $html = preg_replace('/([\n\r \A])(https?:\/\/([^\n\r"< \Z]+))/i',
      '\\1<a href="\\2">\\3</a>', $html);
    return '<p>' . $html . '</p>';
  }

  public function fromHtml($html) {
    $text = str_replace("\n", '', $html);
    $text = preg_replace('/^<p>(.*)<\/p>$/is', "\\1", $text);
    $text = preg_replace('/<\/p> *<p>/i', "\n\n", $text);
    $text = preg_replace('/<br *\/?>/i', "\n", $text);
    $text = preg_replace(
      '/([\n\r \A])<a href="(https?:\/\/.+?)">(.*?)<\/a>([\n\r "]|\Z)/i',
      '\\1\\2\\4', $text);
    return $text;
  }
}
