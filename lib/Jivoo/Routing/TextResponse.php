<?php
/**
 * An HTTP response containing text.
 * @package Jivoo\Routing
 */
class TextResponse extends Response {
  /**
   * @var string Text.
   */
  private $text;

  /**
   * Construct text response.
   * @param int $status HTTP status code.
   * @param string $type Response type.
   * @param string $text Response body.
   */
  public function __construct($status, $type, $text) {
    parent::__construct($status, $type);
    $this->text = $text;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return $this->text;
  }
}
