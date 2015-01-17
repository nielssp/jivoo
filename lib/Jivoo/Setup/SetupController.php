<?php
/**
 * Base class for setup controllers.
 * @package Jivoo\Setup
 */
class SetupController extends Controller {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Setup');

  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Setup');
  
  /**
   * Display in case the configuration cannot be saved.
   * @return ViewResponse Response.
   */
  public function saveConfig() {
    $this->title = tr('Unable to save configuration file');
    $this->file = $this->config->file;
    $this->exists = file_exists($this->file);
    if ($this->exists) {
      $perms = fileperms($this->file);
      $this->mode = sprintf('%o', $perms & 0777);
    }
    $this->data = '<?php' . PHP_EOL . 'return ' . $this->config->prettyPrint() . ';';
    return $this->render('setup/save-config.html');
  }
}
