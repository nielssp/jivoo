<?php
// Module
// Name           : Theme
// Version        : 0.2.0
// Description    : The PeanutCMS theme system
// Author         : PeanutCMS
// Dependencies   : errors configuration templates

/*
 * Class for loading and manipulating the theme
 *
 * @package PeanutCMS
 */

/**
 * Theme class
 */
class Theme implements IModule {

  private $core;
  private $errors;
  private $configuration;
  private $templates;

  /**
   * The current theme
   * @var string
   */
  private $theme;

  private $menuList;

  public function __construct(Core $core) {
    $this->core = $core;
    $this->templates = $this->core->templates;
    $this->errors = $this->core->errors;
    $this->configuration = $this->core->configuration;

    // Set default settings
    if (!$this->configuration->exists('theme.name')) {
      $this->configuration->set('theme.name', 'arachis');
    }

    // Create meta-tags
    if (!$this->templates->hideIdentity()) {
      $this->templates->insertMeta(
        'generator',
        'PeanutCMS' . ($this->templates->hideVersion() ? '' : ' ' . PEANUT_VERSION)
      );
    }
    if ($this->configuration->exists('site.description')) {
      $this->templates->insertMeta(
        'description',
        $this->configuration->get('site.description')
      );
    }

    // Find and load theme
    if ($this->load()) {
      $this->templates->setTheme(p(THEMES . $this->theme . 'templates/'));
    }
    else {
      new GlobalWarning(tr('Please install a theme'), 'theme-missing');
    }
  }

  /**
   * Find and load theme
   *
   * @return bool False if no theme could be loaded
   */
  private function load() {
    if ($this->configuration->exists('theme.name')) {
      $theme = $this->configuration->get('theme.name');
      if (file_exists(p(THEMES . $theme . '/functions.php'))) {
        ob_start();
        require_once(p(THEMES . $theme . '/functions.php'));
        $theme_output = ob_get_clean();
        if ($theme_output != '') {
          $this->errors->log(
              'warning',
              tr('The theme "%1" produced output too early', $theme),
              p(THEMES . $theme . '/functions.php')
            );
        }
        $this->theme = $theme;
        return FALSE;
      }
    }
    if (!is_dir(p(THEMES))) {
      return FALSE;
    }
    $dir = opendir(p(THEMES));
    if ($dir) {
      while (($theme = readdir($dir)) !== false) {
        if (is_dir(p(THEMES . $theme)) AND $theme != '.' AND $theme != '..') {
          if (file_exists(p(THEMES . $theme . '/functions.php'))) {
            $this->configuration->set('theme', $theme);
            ob_start();
            require_once(p(THEMES . $theme . '/functions.php'));
            $theme_output = ob_get_clean();
            if ($theme_output != '') {
              $this->errors->log(
                  'warning',
                  tr('The theme "%1" produced output too early', $theme),
                  p(THEMES . $theme . '/functions.php')
                );
            }
            $this->theme = $theme;
            return FALSE;
          }
        }
      }
      closedir($dir);
    }
    return FALSE;
  }


}

