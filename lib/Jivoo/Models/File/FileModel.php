<?php
/**
 * The file model.
 */
class FileModel extends BasicModel {
  /**
   * @var FileModel Singleton instance.
   */
  private static $instance = null;

  /**
   * Construct file model.
   */
  public function __construct() {
    parent::__construct('File');
    $this->addField('path', tr('Path'), DataType::string());
    $this->addField('name', tr('Name'), DataType::string());
    $this->addField('type', tr('Type'), DataType::enum(array('directory', 'file')));
    $this->addField('size', tr('Size'), DataType::integer(DataType::UNSIGNED));
    $this->addField('modified', tr('Modified'), DataType::dateTime());
    $this->addField('created', tr('Created'), DataType::dateTime());
  }
  
  /**
   * Get singleton instance.
   * @return FileModel Instance.
   */
  public static function getInstance() {
    if (!isset(self::$instance))
      self::$instance = new FileModel();
    return self::$instance;
  }
}