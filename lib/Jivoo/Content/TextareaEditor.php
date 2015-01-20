<?php
/**
 * A simple textarea-based editor for any format.
 * @package Jivoo\Content
 */
class TextareaEditor implements IEditor {
  /**
   * @var string Name of content format.
   */
  private $format;
  
  /**
   * Construct textarea editor.
   * @param string $format Name of content format.
   */
  public function __construct($format) {
    $this->format = $format;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormat() {
    return $this->format;
  }

  /**
   * {@inheritdoc}
   */
  public function field(FormHelper $Form, $field, $options = array()) {
    return $Form->textarea($field, $options);
  }
}
