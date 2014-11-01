<?php
class ExtensionModel extends BasicModel {
  private static $instance = null;
  
  public function __construct($name = 'Extension') {
    parent::__construct($name);
    $this->addField('canonicalName', tr('Canonical name'), DataType::string());
    $this->addField('name', tr('Name'), DataType::string());
    $this->addField('version', tr('Version'), DataType::string());
    $this->addField('description', tr('Description'), DataType::text());
  }
  
  public static function getInstance() {
    if (!isset(self::$instance))
      self::$instance = new ExtensionModel();
    return self::$instance;
  }
}