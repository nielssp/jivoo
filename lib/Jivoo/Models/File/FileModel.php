<?php
class FileModel implements IBasicModel {
  private static $fields = array(
    'path', 'name', 'type', 'size', 'modified', 'created'
  );
  
  private static $types = null;
  private static $labels = null;
  
  public function getName() {
    return 'File';
  }
  
  public function getFields() {
    return self::$fields;
  }
  
  public function getType($field) {
    if (!isset(self::$types)) {
      self::$types = array(
        'path' => DataType::string(),
        'name' => DataType::string(),
        'type' => DataType::enum(array('directory', 'file')),
        'size' => DataType::integer(DataType::UNSIGNED),
        'modified' => DataType::dateTime(),
        'created' => DataType::dateTime(),
      );
    }
    if (isset(self::$types[$field]))
      return self::$types[$field];
    return null;
  }
  
  public function getLabel($field) {
    if (!isset(self::$labels)) {
      self::$labels = array(
        'path' => tr('Path'),
        'name' => tr('Name'),
        'type' => tr('Type'),
        'size' => tr('Size'),
        'modified' => tr('Modified'),
        'created' => tr('Created'),
      );
    }
    if (isset(self::$labels[$field]))
      return self::$labels[$field];
    return null;
  }
  
  public function hasField($field) {
    return in_array($field, self::$fields);
  }
  
  public function isRequired($field) {
    return true;
  }
}