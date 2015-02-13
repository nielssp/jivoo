<?php
/**
 * A directory record.
 */
class DirectoryRecord extends FileRecord {
  /**
   * {@inheritdoc}
   */
  public function __get($field) {
    if ($field == 'type')
      return 'directory';
    return parent::__get($field);
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    $data = parent::getData();
    $data['type'] = 'directory';
    return $data;
  }
  
  /**
   * Get content of directory.
   * @return FileRecord[] List of file records.
   */
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