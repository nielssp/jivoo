<?php
class ThemeModel extends ExtensionModel {
  private static $instance = null;
  
  public function __construct($name = 'Theme') {
    parent::__construct($name);
    $this->addField('screenshot', tr('Screenshot'), DataType::string());
  }
  
  public static function getInstance() {
    if (!isset(self::$instance))
      self::$instance = new ThemeModel();
    return self::$instance;
  }
}