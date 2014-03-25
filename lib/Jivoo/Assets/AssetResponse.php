<?php

class AssetResponse extends Response {

  private $file;

  public function __construct($file) {
    parent::__construct(Http::OK, Utilities::getContentType($file));
    $this->file = $file;
    $this->modified = filemtime($file);
  }

  public function getBody() {
    return file_get_contents($this->file);
  }

}
