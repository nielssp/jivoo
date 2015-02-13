<?php
/**
 * Model for theme information. Can be used as a singleton, use
 * {@see getInstance()} to get instance.
 * @see ThemeInfo
 */
class ThemeModel extends ExtensionModel {
  /**
   * @var ThemeModel Singleton instance.
   */
  private static $instance = null;

  /**
   * Construct model.
   * @param string $name Name of model.
   */
  public function __construct($name = 'Theme') {
    parent::__construct($name);
    $this->addField('screenshot', tr('Screenshot'), DataType::string());
  }
  
  /**
   * Get singleton instance of model.
   * @return ThemeModel Model instance.
   */
  public static function getInstance() {
    if (!isset(self::$instance))
      self::$instance = new ThemeModel();
    return self::$instance;
  }
}