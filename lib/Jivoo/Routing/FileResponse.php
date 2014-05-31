<?php
class FileResponse extends Response {
  private $file;

  public function __construct($status, $type, $file) {
    parent::__construct($status, $type);
    $this->file = $file;
  }

  public function getBody() {
    // TODO readfile() .. e.g. don't buffer output for big files
    return file_get_contents($this->file);
  }
}
