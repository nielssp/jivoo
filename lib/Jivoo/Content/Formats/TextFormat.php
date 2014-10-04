<?php
/**
 * @package Jivoo\Content\Formats
 */
class TextFormat implements IContentFormat {
  public function getName() {
    return 'text';
  }
  public function toHtml($text) {
    return nl2br(h($text));
  }

  public function toText($text) {
    return $text;
  }
}
