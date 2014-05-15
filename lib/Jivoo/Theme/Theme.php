<?php
// Module
// Name           : Theme
// Description    : The Jivoo theme system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Templates Jivoo/Assets

/**
 * Class for loading and managing themes
 * @package Jivoo\Theme
 */
class Theme extends LoadableModule {
  
  protected $modules = array('Templates', 'Assets');
  
  /**
   * @var string The current theme
   */
  private $theme;

  private $menuList;

  protected function init() {
    // Create meta-tags
    $this->view->meta('generator', 'Jivoo');
    //     if ($this->m
//       ->Configuration
//       ->exists('site.description')) {
//       $this->m
//         ->Templates
//         ->insertMeta('description',
//           $this->m
//             ->Configuration
//             ->get('site.description'));
//     }

    // Find and load theme
    if (!$this->loadThemeFor('frontend', 9)) {
      if (is_dir($this->p('themes', ''))) {
        $dir = opendir($this->p('themes', ''));
        if ($dir) {
          while (($theme = readdir($dir)) !== false) {
            if (is_dir($this->p('themes', $theme)) AND $theme != '.'
              AND $theme != '..') {
              if (file_exists($this->p('themes', $theme . '/' . $theme . '.php'))) {
                $this->config['frontend'] = $theme;
                $this->setTheme($theme, 9);
              }
            }
          }
          closedir($dir);
        }
      }
    }
    
//     $this->m->Backend['appearance']->setup(tr('Appearance'), 4)
//       ->item(tr('Themes'), null, 0)
//       ->item(tr('Customize'), null, 0);
  }
  
  public function setTheme($theme, $priority = 10) {
    $this->view->addTemplateDir(
      $this->p('themes', $theme . '/templates'),
      $priority
    );
    $this->m->Assets->addAssetDir(
      'themes',
      $theme . '/assets',
      $priority
    );
  }
  
  /**
   * Load a theme for a part of the application
   * @param string $zone A part of the application, e.g. 'frontend', 'backend'
   * @param int $priority Priority of templates in theme 
   * @return bool True if theme is set, false otherwise
   */
  public function loadThemeFor($zone = 'frontend', $priority = 10) {
    $theme = $this->load($zone);
    if ($theme !== false) {
      $this->setTheme($theme, $priority);
      return true;
    }
    return false;
  }

  /**
   * Find and load theme
   * @param string $zone A part of the application, e.g. 'frontend', 'backend'
   * @return string|false Theme name or false if no theme could be loaded
   */
  private function load($zone = 'frontend') {
    if (isset($this->config[$zone])) {
      $theme = $this->config[$zone];
      if (file_exists($this->p('themes', $theme . '/' . $theme . '.php'))) {
        return $theme;
      }
    }
    return false;
  }

}

