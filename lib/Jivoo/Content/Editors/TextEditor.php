<?php
/**
 * Like {@see HtmlEditor}, but its content format, {@see TextFormat}, will
 * automatically convert line breaks and links.
 * @package Jivoo\Editors
 */
class TextEditor extends HtmlEditor {
  public function saveFilter($text) {
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
  public function field(FormHelper $Form, $field, $options = array()) {
    $options['value'] = $this->fromHtml($Form->value($field));
    return $Form->textarea($field, $options);
  }
  
  public function fromHtml($html) {
    $text = str_replace("\n", '', $html);
    $text = preg_replace('/^<p>(.*)<\/p>$/is', "\\1", $text);
    $text = preg_replace('/<\/p> *<p>/i', "\n\n", $text);
    $text = preg_replace('/<br *\/?>/i', "\n", $text);
    $text = preg_replace(
      '/<a href="(https?:\/\/.+?)">(.*?)<\/a>([\n\r "()]|\Z)/i',
      '\\1\\3', $text);
    return $text;
  }
}
