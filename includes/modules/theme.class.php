<?php
/*
 * Class for loading and manipulating the theme
 *
 * @package PeanutCMS
 */

/**
 * Theme class
 */
class Theme {

  /**
   * The current theme
   * @var string
   */
  var $theme;

  /**
   * HTML-code to be inserted on page
   * @var array
   */
  var $html;
  
  var $menuList;

  /**
   * PHP5-style constructor
   */
  function __construct() {
    global $PEANUT;

    $this->html = array();

    // Set default settings
    if (!$PEANUT['configuration']->exists('theme'))
      $PEANUT['configuration']->set('theme', 'arachis');
    if (!$PEANUT['configuration']->exists('title'))
      $PEANUT['configuration']->set('title', 'PeanutCMS');
    if (!$PEANUT['configuration']->exists('subtitle'))
      $PEANUT['configuration']->set('subtitle', 'The domesticated peanut is an amphidiploid or allotetraploid.');

    // Create meta-tags
    $this->insertHtml('meta-generator', 'head-top', 'meta', array('name' => 'generator', 'content' => 'PeanutCMS ' . PEANUT_VERSION), '', 8);
    $this->insertHtml('meta-description', 'head-top', 'meta', array('name' => 'description', 'content' => $PEANUT['configuration']->get('subtitle')), '', 6);


    // Get defaut theming functions
    require_once(PATH . INC . 'helpers/theme-helpers.php');

    // Find and load theme
    if (!$this->load())
      $PEANUT['errors']->notification('warning', tr('Please install a theme'), true, 'theme-missing', 'http://google.com');
  }

  /**
   * PHP5-style destructor
   *
   * @return bool true
   */
  function __destruct() {
    return true;
  }

  /**
   * Find and load theme
   *
   * @return bool False if no theme could be loaded
   */
  function load() {
    global $PEANUT;
    if ($PEANUT['configuration']->exists('theme')) {
      $theme = $PEANUT['configuration']->get('theme');
      if (file_exists(PATH . THEMES . $theme . '/functions.php')) {
        ob_start();
        require_once(PATH . THEMES . $theme . '/functions.php');
        $theme_output = ob_get_clean();
        if ($theme_output != '')
          $PEANUT['errors']->log('warning', tr('The theme "%1" produced output too early', $theme), PATH . THEMES . $theme . '/functions.php');
        $this->theme = $theme;
        return true;
      }
    }
    $dir = opendir(PATH . THEMES);
    if ($dir) {
      while (($theme = readdir($dir)) !== false) {
        if (is_dir(PATH . THEMES . $theme) AND $theme != '.' AND $theme != '..') {
          if (file_exists(PATH . THEMES . $theme . '/functions.php')) {
            $PEANUT['configuration']->set('theme', $theme);
            ob_start();
            require_once(PATH . THEMES . $theme . '/functions.php');
            $theme_output = ob_get_clean();
            if ($theme_output != '')
              $PEANUT['errors']->log('warning', tr('The theme "%1" produced output too early', $theme), PATH . THEMES . $theme . '/functions.php');
            $this->theme = $theme;
            return true;
          }
        }
      }
      closedir($dir);
    }
  }
  
  function listMenu() {
    global $PEANUT;
    if (is_array($this->menuList))
      return next($this->menuList);
    $menu = $PEANUT['configuration']->get('menu');
    $this->menuList = array();
    foreach ($menu as $menuitem) {
      $menuitem['link'] = $PEANUT['http']->getLink($PEANUT['templates']->getPath($menuitem['template'], $menuitem['parameters']));
      $menuitem['selected'] = $menuitem['link'] == $PEANUT['http']->getLink();
      $this->menuList[] = $menuitem;
    }
    ksort($this->menuList);
    reset($this->menuList);
    return current($this->menuList);
  }

  /**
   * Return a link to a file in the current theme
   *
   * @param string $file File name
   * @return string Link
   */
  function getFile($file) {
    if (isset($this->theme) AND file_exists(PATH . THEMES . $this->theme . '/' . $file)) {
      return WEBPATH . THEMES . $this->theme . '/' . $file;
    }
    if (file_exists(PATH . PUB . $file)) {
      return WEBPATH . PUB . $file;
    }
  }

  /**
   * Insert an HTML-tag (e.g. a script, stylesheet, meta-tag etc.) on the page
   *
   * @param string $id Id
   * @param string $location Location on page (e.g. 'head-top', 'head-bottom', 'body-top' or 'body-bottom')
   * @param string $tag HTML-tag (e.g. 'meta', 'link', 'script', 'style' etc.)
   * @param array $parameters HTML-parameters (e.g. array('src' => 'somescript.js'))
   * @param string $innerhtml Optional string to be placed between start- and end-tag
   * @param int $priority A high-priority (e.g. 10) tag will be inserted before a low-priority one (e.g 2)
   */
  function insertHtml($id, $location, $tag, $parameters, $innerhtml = '', $priority = 5) {
    $tag = strtolower($tag);
    if ($tag == 'script' AND !isset($parameters['type']))
      $parameters['type'] = 'text/javascript';
    if ($tag == 'style' AND !isset($parameters['type']))
      $parameters['type'] = 'text/css';
    $this->html[$location][$id] = array('tag' => $tag,
                                        'innerhtml' => $innerhtml,
                                        'priority' => $priority,
                                        'parameters' => $parameters);
  }
  
  /**
   * Output HTML-code attached to a location on the page
   *
   * @param string $location Location on page (e.g. 'head-top', 'head-bottom', 'body-top' or 'body-bottom')
   */
  function outputHtml($location) {
    if (!isset($this->html[$location]) OR !is_array($this->html[$location]))
      return;
    uasort($this->html[$location], 'prioritySorter');
    foreach ($this->html[$location] as $id => $html) {
      echo '<' . $html['tag'];
      foreach ($html['parameters'] as $parameter => $value)
        echo ' ' . $parameter . '="' . addslashes($value) . '"';
      if (empty($html['innerhtml']) AND $html['tag'] != 'script') {
        echo ' />';
      }
      else {
        echo '>';
        if (!empty($html['innerhtml'])) {
          $this->outputHtml($id . '-top');
          echo $html['innerhtml'];
          $this->outputHtml($id . '-bottom');
        }
        echo '</' . $html['tag'] . '>';
      }
      echo "\n";
    }
  }

}