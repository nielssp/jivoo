<?php
/**
 * Setup controller base 
 * @package Jivoo\Setup
 */
class SetupController extends Controller {
  
  protected $modules = array('Setup');
  protected $helpers = array('Setup');
  
  public function saveConfig() {
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
