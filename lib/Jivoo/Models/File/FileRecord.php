<?php
class FileRecord implements IBasicRecord {
  private $model;
  
  private $path;
  private $name;
  private $type = 'file';
  private $size = null;
  private $modified = null;
  private $created = null;
  
  public function __construct(FileModel $model, $path) {
    $this->model = $model;
    $this->path = $path;
    $this->name = basename($path);
  }
  
  public function __get($field) {
    switch ($field) {
      case 'path':
      case 'name':
      case 'type':
        return $this->$field;
      case 'size':
        if (!isset($this->size))
          $this->size = filesize($this->path);
        return $this->size;
      case 'modified':
        if (!isset($this->modified))
          $this->modified = filemtime($this->path);
        return $this->modified;
      case 'created':
        if (!isset($this->created))
          $this->created = filectime($this->path);
        return $this->created;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $field));
  }
  
  public function __isset($field) {
    return $this->__get($field) !== null;
  }
  
  public function getData() {
    return array(
      'path' => $this->path,
      'name' => $this->name,
      'type' => $this->type,
      'size' => $this->size,
      'modified' => $this->modified,
      'created' => $this->created
    );
  }
  
  public function getModel() {
    return $this->model;
  }
  
  public function getErrors() {
    return array();
  }
  
  public function isValid() {
    return true;
  }
  
  public function offsetExists($field) {
    return $this->__isset($field);
  }

  public function offsetGet($field) {
    return $this->__get($field);
  }

  public function offsetSet($field, $value) {
  }

  public function offsetUnset($field) {
  }
}