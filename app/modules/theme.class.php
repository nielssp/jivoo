<?php
/*
 * Class for loading and manipulating the theme
 *
 * @package PeanutCMS
 */

/**
 * Theme class
 */
class Theme implements IModule {


  private $errors;

  private $configuration;

  private $templates;

  public function getConfiguration() {
    return $this->configuration;
  }

  public function getErrors() {
    return $this->errors;
  }

  public function getTemplates() {
    return $this->templates;
  }

  /**
   * The current theme
   * @var string
   */
  private $theme;

  private $menuList;

  public function __construct(Templates $templates) {
    $this->templates = $templates;
    $this->errors = $this->templates->getErrors();
    $this->configuration = $this->templates->getConfiguration();

    // Set default settings
    if (!$this->configuration->exists('theme.name')) {
      $this->configuration->set('theme.name', 'arachis');
    }

    // Create meta-tags
    if (!$this->templates->hideIdentity()) {
      $this->templates->insertHtml(
        'meta-generator',
        'head-top',
        'meta',
        array(
          'name' => 'generator',
          'content' => 'PeanutCMS' . ($this->templates->hideVersion() ? '' : ' ' . PEANUT_VERSION)
        ),
        '',
        8
      );
    }
    if ($this->configuration->exists('site.description')) {
      $this->templates->insertHtml(
        'meta-description',
        'head-top',
        'meta',
        array(
          'name' => 'description',
          'content' => $this->configuration->get('site.description')
        ),
        '',
        6
      );
    }

    // Find and load theme
    if ($this->load()) {
      $this->templates->setTheme(p(THEMES . $this->theme . 'templates/'));
    }
    else {
      $this->errors->notification('warning', tr('Please install a theme'), true, 'theme-missing', 'http://google.com');
    }
  }

  public static function getDependencies() {
    return array('templates');
  }

  /**
   * Find and load theme
   *
   * @return bool False if no theme could be loaded
   */
  private function load() {
    if ($this->configuration->exists('theme')) {
      $theme = $this->configuration->get('theme');
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
