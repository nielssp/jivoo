<?php
// Module
// Name           : Theme
// Description    : The PeanutCMS theme system
// Author         : apakoh.dk
// Dependencies   : Core/Templates Core/Assets PeanutCMS/Backend

/**
 * Class for loading and managing themes
 * @package PeanutCMS\Theme
 */
class Theme extends ModuleBase {

  /**
   * @var string The current theme
   */
  private $theme;

  private $menuList;

  protected function init() {
    // Create meta-tags
    $this->view->meta('generator', 'PeanutCMS');
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
    if ($this->load()) {
      $this->view->addTemplateDir(
        $this->p('themes', $this->theme . '/templates'),
        10
      );
      $this->m->Assets->addAssetDir(
        'themes',
        $this->theme . '/assets',
        10
      );
    }
    else {
//       new GlobalWarning(tr('Please install a theme'), 'theme-missing');
    }
    
    $this->m->Backend['appearance']->setup(tr('Appearance'), 4)
      ->item(tr('Themes'), null, 0)
      ->item(tr('Customize'), null, 0);
  }

  /**
   * Find and load theme
   *
   * @return bool False if no theme could be loaded
   */
  private function load() {
    if (isset($this->config['name'])) {
      $theme = $this->config['name'];
      if (file_exists($this->p('themes', $theme . '/' . $theme . '.php'))) {
        $this->theme = $theme;
        return true;
      }
    }
    if (!is_dir($this->p('themes', ''))) {
      return false;
    }
    $dir = opendir($this->p('themes', ''));
    if ($dir) {
      while (($theme = readdir($dir)) !== false) {
        if (is_dir($this->p('themes', $theme)) AND $theme != '.'
            AND $theme != '..') {
          if (file_exists($this->p('themes', $theme . '/' . $theme . '.php'))) {
            $this->config['name'] = $theme;
            $this->theme = $theme;
            return true;
          }
        }
      }
      closedir($dir);
    }
    return false;
  }

}

