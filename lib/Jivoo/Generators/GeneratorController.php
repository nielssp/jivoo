<?php
/**
 * Controller for generators
 * @package Jivoo\Generators
 */
class GeneratorController extends Controller {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Generators');

  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Html', 'Form');
  
  /**
   * Application generation frontpage.
   * @return ViewResponse Response.
   */
  public function index() {
    $this->title = tr('Generate application');
    $this->appDir = $this->p('app');
    return $this->render();
  }
  
  private function getModules() {
    $files = scandir(LIB_PATH . '/Jivoo');
    $modules = array();
    if ($files !== false) {
      foreach ($files as $file) {
        if ($file[0] == '.')
          continue;
        $module = 'Jivoo/' . $file;
        if (is_dir(LIB_PATH . '/' . $module)) {
          $modules[] = $module;
        }
      }
    }
    return $modules;
  }

  /**
   * App config generation.
   * @return ViewResponse Response.
   */
  public function configure() {
    $this->title = tr('Configure applciation');
    $this->configForm = new Form('App');
    $this->configForm->addString('name', tr('Application name'));
    $this->configForm->addString('version', tr('Version'));
    $this->availableModules = $this->getModules();
    if ($this->request->hasValidData('App')) {
      
    }
    else {
      $this->configForm->name = $this->app->name;
      $this->configForm->version = $this->app->version;
    }
    return $this->render();
  }
}
