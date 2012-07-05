<?php
class HtmlFormat implements IContentFormat {
  public function configure(Configuration $config) {
    //
  }
  
  public function toHtml($text) {
    /** @todo only allow certain tags/attributes */
    return $text;
  }
  
  public function fromHtml($html) {
    return $html;
  }
}