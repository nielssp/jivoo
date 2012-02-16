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
  
  function setContentType($name) {
    global $PEANUT;
    $fileName = explode('.', $name);
    $fileExt = $fileName[count($fileName) - 1];
    $contentType = null;
    switch ($fileExt) {
      case 'html':
      case 'htm':
        $contentType = "text/html";
        $PEANUT['theme']->insertHtml('meta-charset', 'head-top', 'meta',
          array('http-equiv' => 'content-type', 'content' => 'text/html;charset=utf-8'), '', 10);
        break;
      case 'css':
        $contentType = "text/css";
        break;
      case 'js':
        $contentType = "text/javascript";
        break;
      default:
        $PEANUT['errors']->fatal(tr('Unsupported content type'),
          tr('Unsupported content type: %1', $fileExt));
        break;
    }
    header('Content-Type:' . $contentType . ';charset=utf-8');    
  }

  function renderTemplate($name, $parameters = array()) {
    global $PEANUT;
    $parameters = array_merge($parameters, $PEANUT['http']->params);
    if (isset($PEANUT['theme']->theme)
        AND file_exists(PATH . THEMES . $PEANUT['theme']->theme . '/' . $name . '.php')) {
      $this->setContentType($name);
      require(PATH . THEMES . $PEANUT['theme']->theme . '/' . $name . '.php');
    }
    else if (file_exists(PATH . INC . 'templates/' . $name . '.php')) {
      $this->setContentType($name);
      require(PATH . INC . 'templates/' . $name . '.php');
    }
    else if (strpos($name, '.') === false) {
      $this->renderTemplate($name . '.html', $parameters);
    }
    else {
      echo '<p>' . tr('The template "%1" could not be found', $name) . '</p>';
    }
//    $this->renderTemplate('default.html', array('content' => tr('The template "%1" could not be found', $name)));
  }

}