<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * Responds with the content of a file.
 */
class FileResponse extends Response {
  /**
   * @var string File path.
   */
  private $file;

  /**
   * Construct file response.
   * @param int $status Status code.
   * @param string $type Response content type.
   * @param string $file File path.
   */
  public function __construct($status, $type, $file) {
    parent::__construct($status, $type);
    $this->file = $file;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    // TODO readfile() .. e.g. don't buffer output for big files
    return file_get_contents($this->file);
  }
}
