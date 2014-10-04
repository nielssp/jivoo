<?php
class MarkdownFormat implements IContentFormat {
  public function getName() {
    return 'markdown';
  }
  public function toHtml($text) {
    $Parsedown = new Parsedown();
    return $Parsedown->text($text);
  }
  public function toText($text) {
    $encoder = new HtmlEncoder();
    return $encoder->encode($this->toHtml($text));
  }
}
