<?php
/**
 * Responds with the content of a file.
 * @package Jivoo\Routing
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
