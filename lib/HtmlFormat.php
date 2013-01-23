<?php
class HtmlFormat implements IContentFormat {
  public function toHtml($text) {
    return html_entity_decode($text, null, 'UTF-8');
  }

  public function fromHtml($html) {
    return $html;
  }
}
