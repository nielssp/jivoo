<?php
// Module
// Name           : Helpers
// Description    : For helpers
// Author         : apakoh.dk
// Dependencies   : Jivoo/Routing Jivoo/Models

/**
 * Helpers module. All helpers added to the module, can be accessed as
 * read-only properties.
 * @package Jivoo\Helpers
 * @property-read HtmlHelper $Html HTML helper
 * @property-read JsonHelper $Json JSON helper
 * @property-read FormHelper $Form Form helper
 */
class Helpers extends LoadableModule {
  
  protected $modules = array('Routing', 'Models');
  
    /**
   * @var array Associative array of helper names and objects
   */
  private $helperObjects = array();
  
  /**
   * @var array Associative array of helper names and class names
   */
  private $helpers = array(
    'Html' => 'HtmlHelper',
    'Json' => 'JsonHelper',
    'Form' => 'FormHelper',
  );
  
  protected function init() {
    $helpersDir = $this->p('helpers', '');
    if (is_dir($helpersDir)) {
      Lib::addIncludePath($helpersDir);
      $dir = opendir($helpersDir);
      if ($dir) {
        while ($file = readdir($dir)) {
          $split = explode('.', $file);
          if (isset($split[1]) AND $split[1] == 'php') {
            $class = $split[0];
            $name = str_replace('Helper', '', $class);
            $this->helpers[$name] = $class;
          }
        }
        closedir($dir);
      }
    }
  }
  
  /**
   * Get instance of helper
   * @param string $name Helper name
   * @return Helper|null A helper object or null on failure
   */
  private function getInstance($name) {
    if (isset($this->helpers[$name])) {
      if (!isset($this->helperObjects[$name])) {
        $class = $this->helpers[$name];
        $this->helperObjects[$name] = new $class($this->m->Routing);
        $helper = $this->helperObjects[$name];

        $modules = $helper->getModuleList();
        foreach ($modules as $moduleName) {
          $module = $this->app->requestModule($moduleName);
          if ($module) {
            $helper->addModule($module);
          }
          else {
            throw new ModuleNotFoundException(tr(
              'Module "%1" not found for helper %2', $moduleName, $name
            ));
          }
        }
        $this->addHelpers($helper);
        $this->m->Models->addModels($helper);
      }
      return $this->helperObjects[$name];
    }
    return null;
  }
  
  /**
   * Add helpers to a helpable object
   * @param IHelpable $helpable Object to accept helpers
   */
  public function addHelpers(IHelpable $helpable) {
    $helpers = $helpable->getHelperList();
    foreach ($helpers as $helperName) {
      $helper = $this->getHelper($helperName);
      if ($helper != null) {
        $helpable->addHelper($helper);
      }
      else {
        Logger::error(tr('Helper "%1" not found for %2', $helperName, get_class($helpable)));
      }
    }
  }
  
  /**
   * Add a helper
   * @param Helper $helper helper
   */
  public function addHelper(Helper $helper) {
    $name = str_replace('Helper', '', get_class($helper));
    $this->helperObjects[$name] = $helper;
  }
  
  /**
   * Get a helper instance
   * @param string $name Helper name
   * @return Helper|null A helper object or null on failure
   */
  public function getHelper($name) {
    if (isset($this->helperObjects[$name])) {
      return $this->helperObjects[$name];
    }
    return $this->getInstance($name);
  }
  
  /**
   * Get a helper instance
   * @param string $name Helper name
   * @return Helper|null A helper object or null on failure
   */
  public function __get($name) {
    return $this->getHelper($name);
  }
}