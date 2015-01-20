<?php
/**
 * Plaintext format.
 * @package Jivoo\Content\Formats
 */
class TextFormat implements IContentFormat {
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'text';
  }

  /**
   * {@inheritdoc}
   */
  public function toHtml($text) {
    return nl2br(h($text));
  }
}
