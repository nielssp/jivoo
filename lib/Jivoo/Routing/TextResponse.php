<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * An HTTP response containing text.
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
