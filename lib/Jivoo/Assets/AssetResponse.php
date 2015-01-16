<?php
/**
 * Responds with the content of an asset.
 * @package Jivoo\Assets
 */
class AssetResponse extends Response {
  /**
   * @var string File path.
   */
  private $file;

  /**
   * Construct asset response.
   * @param string $file Path to asset.
   */
  public function __construct($file) {
    parent::__construct(Http::OK, Utilities::getContentType($file));
    $this->file = $file;
    $this->modified = filemtime($file);
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return file_get_contents($this->file);
  }

}
