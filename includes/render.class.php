<?php
/*
 * Class for rendering the page
 *
 * @package PeanutCMS
 */

/**
 * Render class
 */
class Render {

  /**
   * Constructor
   */
  function Render() {
    return $this->__construct();
  }

  /**
   * PHP5-style constructor
   */
  function __construct() {
    global $PEANUT;
    
    /**
     * @todo Debugging
     */
    if ($PEANUT['actions']->has('unset'))
      session_unset();

    $PEANUT['hooks']->run('preRender');
    
    $PEANUT['templates']->setFinal();
    
    $PEANUT['hooks']->run('finalTemplate');

    // Set charset
    header("Content-Type:text/html;charset=utf-8");
    $PEANUT['theme']->insertHtml('meta-charset', 'head-top', 'meta', array('http-equiv' => 'content-type', 'content' => 'text/html;charset=utf-8'), '', 10);

    // Render theme
    $this->renderPage();

    $PEANUT['hooks']->run('postRender');
  }

  /**
   * PHP5-style destructor
   *
   * @return bool true
   */
  function __destruct() {
    return true;
  }

  function renderPage() {
    global $PEANUT;
    $this->renderTemplate($PEANUT['templates']->template['name']);
  }

  function renderTemplate($name, $parameters = array()) {
    global $PEANUT;
    $parameters = array_merge($parameters, $PEANUT['http']->params);
    if (isset($PEANUT['theme']->theme) AND file_exists(PATH . THEMES . $PEANUT['theme']->theme . '/' . $name . '.tmp.php')) {
      require(PATH . THEMES . $PEANUT['theme']->theme . '/' . $name . '.tmp.php');
      return;
    }
    if (file_exists(PATH . INC . 'templates/' . $name . '.tmp.php')) {
      require(PATH . INC . 'templates/' . $name . '.tmp.php');
      return;
    }
    if ($name == 'default') {
      echo '<p>' . tr('The template "%1" could not be found', $name) . '</p>';
      return;
    }
    $this->renderTemplate('default', array('content' => tr('The template "%1" could not be found', $name)));
  }

}