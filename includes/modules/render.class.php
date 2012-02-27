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
   * PHP5-style constructor
   */
  public function __construct() {
    global $PEANUT;

    /**
     * @todo Debugging
     */
    if ($PEANUT['actions']->has('unset')) {
      session_unset();
    }

    $PEANUT['hooks']->run('preRender');

    $PEANUT['hooks']->run('finalTemplate');

    $PEANUT['routes']->callController();

    $PEANUT['hooks']->run('postRender');
  }


  private function setContentType($name) {
    global $PEANUT;
    $fileName = explode('.', $name);
    $fileExt = $fileName[count($fileName) - 1];
    $contentType = null;
    switch ($fileExt) {
      case 'html':
      case 'htm':
        $contentType = "text/html";
        $PEANUT['theme']->insertHtml(
          'meta-charset', 'head-top', 'meta',
          array('http-equiv' => 'content-type', 'content' => 'text/html;charset=utf-8'),
          '', 10
        );
        break;
      case 'css':
        $contentType = "text/css";
        break;
      case 'js':
        $contentType = "text/javascript";
        break;
      default:
        $PEANUT['errors']->fatal(
          tr('Unsupported content type'),
          tr('Unsupported content type: %1', $fileExt)
        );
        break;
    }
    header('Content-Type:' . $contentType . ';charset=utf-8');
  }

  public function renderTemplate($name, $parameters = array()) {
    global $PEANUT;
    extract($parameters, EXTR_SKIP);
    if (isset($PEANUT['theme']->theme)
        AND file_exists(PATH . THEMES . $PEANUT['theme']->theme . '/templates/' . $name . '.php')) {
      $this->setContentType($name);
      require(PATH . THEMES . $PEANUT['theme']->theme . '/templates/' . $name . '.php');
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