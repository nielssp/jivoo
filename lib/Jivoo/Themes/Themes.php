<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Themes;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Json;
use Jivoo\Core\Logger;

/**
 * Theming module.
 */
class Themes extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Extensions', 'View', 'Assets');
  
  /**
   * @var ThemeInfo[] Associative array of theme names and theme information.
   */
  private $info = array();
  
  /**
   * @var string[] Where to look for themes (path keys).
   */
  private $libraries = array('app', 'share');
  
  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->m->Extensions->addKind('themes', 'theme');
  }
  
  /**
   * Get the name of a theme associated with the given zone.
   * @param string $zone Zone name, e.g. "frontend", "admin", etc.
   * @throws ThemeNotFoundException If no theme could be found for the
   * sepcified zone.
   * @return Config
   */
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

  /**
   * Get information about a theme.
   * @param string $theme Theme name.
   * @return ThemeInfo|null Theme information or null if theme not found or
   * invalid. Will produce a warning if the JSON file is invalid.
   */
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
  
  /**
   * Check dependencies for a theme.
   * @param ThemeInfo $info Theme information.
   * @return true|array Returns true if no dependencies are missing, otherwise
   * returns an associative array structure listing missing dependencies of
   * different categories,s ee {@see Extensions::checkDependencies()}.
   */
  public function checkDependencies(ThemeInfo $info) {
    return $this->m->Extensions->checkDependencies($info);
  }
  
  /**
   * Load the templates and assets of a theme.
   * @param string $theme Theme name.
   * @param int $priority Priority of templates, if the theme has one or more
   * parent themes, those themes will be loaded with a lower priority.
   * @throws ThemeNotFoundException If the theme was not found or invalid.
   */
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
  
  /**
   * List all themes.
   * @return ThemeInfo[] List of theme infos.
   */
  public function listAllThemes() {
    $themes = $this->listThemes();
    foreach ($this->libraries as $library)
      $themes = array_merge($themes, $this->listThemes($library));
    return $themes;
  }
  
  /**
   * List themes found in a specific library (default is user-library).
   * @param string $library Library (path key).
   * @return ThemeInfo[] List of theme infos.
   */
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
  
  /**
   * Whether or not a theme is enabled.
   * @param string $theme Theme name.
   * @return boolean True if enabled, false otherwise.
   */
  public function isEnabled($theme) {
    return false;
  }
  
  /**
   * Enable a theme.
   * @param string $theme Theme name.
   */
  public function enable($theme) {
    $info = $this->getInfo($theme);
    
  }
}

/**
 * Thrown if a theme could not be found.
 */
class ThemeNotFoundException extends \Exception { }