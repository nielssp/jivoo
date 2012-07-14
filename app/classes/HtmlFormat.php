<?php
class HtmlFormat implements IContentFormat {
  public function toHtml($text) {
    return $text;
  }
  
  public function fromHtml($html) {
    return $html;
  }
}
