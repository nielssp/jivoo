<?php
class Themes extends LoadableModule {
  protected $modules = array('Extensions', 'View', 'Assets');
  
  private $info = array();
  
  protected function init() {
    $this->m->Extensions->addKind('themes', 'theme');
    $this->load($this->zone('main'));
  }
  
  public function zone($zone) {
    if (!isset($this->config[$zone])) {
      $themes = $this->listThemes();
      foreach ($themes as $info) {
        if (in_array($zone, $info->zones)) {
          $this->config[$zone] = $info->canonicalName;
          break;
        }
      }
      if (!isset($this->config[$zone]))
        throw new ThemeNotFoundException(tr('No theme found for "%1"', $zone));
    }
    return $this->config[$zone];
  }

  public function getInfo($theme) {
    if (!isset($this->info[$theme])) {
      $dir = $this->p('themes', $theme);
      $bundled = false;
      if (!file_exists($dir . '/theme.json')) {
        $dir = $this->p('app', 'themes/' . $theme);
        $bundled = true;
        if (!file_exists($dir . '/theme.json'))
          return null;
      }
      $info = Json::decodeFile($dir . '/theme.json');
      if (!$info)
        return null;
      $this->info[$theme] = new ThemeInfo($theme, $info, $bundled, array());
    }
    return $this->info[$theme];
  }
  
  public function checkDependencies(ThemeInfo $info) {
    return $this->m->Extensions->checkDependencies($info);
  }
  
  public function load($theme, $priority = 10) {
    $info = $this->getInfo($theme);
    if (!isset($info))
      throw new ThemeNotFoundException(tr('Theme not found or invalid: "%1"', $theme));
    foreach ($info->extend as $parent) {
      $this->load($parent, $zone);
    }
    $this->view->addTemplateDir(
      $info->p($this->app, 'templates'),
      $priority
    );
    $info->addAssetDir($this->m->Assets, 'assets');
  }
  
  public function listThemes() {
    $files = scandir($this->p('themes', ''));
    $themes= array();
    if ($files !== false) {
      foreach ($files as $file) {
        $info = $this->getInfo($file);
        if (isset($info))
          $themes[] = $info;
      }
    }
    return $themes;
  }

  
  public function isEnabled($theme) {
    return false;
  }
  
  public function enable($theme) {
    $info = $this->getInfo($theme);
    
  }
}

class ThemeNotFoundException extends Exception { }