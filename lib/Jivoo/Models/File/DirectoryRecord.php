<?php
class DirectoryRecord extends FileRecord {
  public function __get($field) {
    if ($field == 'type')
      return 'directory';
    return parent::__get($field);
  }
  public function getData() {
    $data = parent::getData();
    $data['type'] = 'directory';
    return $data;
  }
  
  public function getContent() {
    $files = scandir($this->path);
    if ($files === false)
      return array();
    $records = array();
    foreach ($files as $file) {
      if ($file[0] != '.') {
        $path = $this->path . '/' . $file;
        if (is_dir($path))
          $records[] = new DirectoryRecord($this->getModel(), $path);
        else
          $records[] = new FileRecord($this->getModel(), $path);
      }
    }
    return $records;
  }
}