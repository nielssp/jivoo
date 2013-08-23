<?php
/**
 * Like {@see HtmlEditor}, but its content format, {@see TextFormat}, will
 * automatically convert line breaks and links.
 */
class TextEditor extends HtmlEditor {
  /**
   * Constructor
   */
  public function __construct() {
    $this->format = new TextFormat();
  }
}
