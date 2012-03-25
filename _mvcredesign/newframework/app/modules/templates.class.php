<?php
/**
 * Class for setting the template
 *
 * @package PeanutCMS
 */

/**
 * Templates class
 */
class Templates implements IModule {

  private $errors;

  private $theme;

  /**
   * PHP5-style constructor
   */
  function __construct(Errors $errors) {
    $this->errors = $errors;

    $this->setTheme(TEMPLATES);

//     if (!$PEANUT['configuration']->exists('menu')) {
//       $PEANUT['configuration']->set(
//         'menu',
//         array(
//           'label'      => tr('Home'),
//           'template'   => 'list-posts',
//           'parameters' => array(
//             'sortDesc' => 'date',
//             'perPage'  => 10
//           )
//         ),
//         array(
//           'label'      => tr('Links'),
//           'template'   => 'page',
//           'parameters' => array('p' => 2)
//         ),
//         array(
//           'label'      => tr('About'),
//           'template'   => 'page',
//           'parameters' => array('p' => 1)
//         )
//       );
//     }

  }

  public static function getDependencies() {
    return array('errors');
  }

  private function setContentType($name) {
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
        $this->errors->fatal(
          tr('Unsupported content type'),
          tr('Unsupported content type: %1', $fileExt)
        );
      break;
    }
    header('Content-Type:' . $contentType . ';charset=utf-8');
  }

  public function setTheme($templateDir) {
    $this->theme = $templateDir;
  }

  public function renderTemplate($name, $parameters = array()) {
    extract($parameters, EXTR_SKIP);
    if (file_exists(p($this->theme . $name. '.php'))) {
      $this->setContentType($name);
      require(p($this->theme . $name . '.php'));
    }
    else if (file_exists(p(TEMPLATES . $name . '.php'))) {
      $this->setContentType($name);
      require(p(TEMPLATES . $name . '.php'));
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
