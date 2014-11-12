<?php
class Themes extends LoadableModule {
  protected $modules = array('Extensions', 'View', 'Assets');
  
  private $info = array();
  
  private $libraries = array('app', 'share');
  
  protected function init() {
    $this->m->Extensions->addKind('themes', 'theme');
    $this->load($this->zone('main'));
  }
  
  public function zone($zone) {
    if (!isset($this->config[$zone])) {
      $themes = $this->listAllThemes();
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
      $library = null;
      if (!file_exists($dir . '/theme.json')) {
        foreach ($this->libraries as $key) {
          $dir = $this->p($key, 'themes/' . $theme);
          if (file_exists($dir . '/theme.json'))
            $library = $key;
        }
        if (!isset($library))
          return null;
      }
      $info = Json::decodeFile($dir . '/theme.json');
      if (!$info) {
        Logger::warning(tr('The theme "%1" has an invalid json file.', $theme));
        return null;
      }
      $this->info[$theme] = new ThemeInfo($theme, $info, array(), $library);
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
      $this->load($parent, $priority - 1);
    }
    $this->view->addTemplateDir(
      $info->p($this->app, 'templates'),
      $priority
    );
    $info->addAssetDir($this->m->Assets, 'assets');
  }
  
  public function listAllThemes() {
    $themes = $this->listThemes();
    foreach ($this->libraries as $library)
      $themes = array_merge($themes, $this->listThemes($library));
    return $themes;
  }
  
  public function listThemes($library = null) {
    if (isset($library))
      $dir = $this->p($library, 'themes');
    else
      $dir = $this->p('themes', '');
    if (!is_dir($dir))
      return array();
    $files = scandir($dir);
    $themes = array();
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