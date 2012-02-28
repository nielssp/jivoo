<?php
/**
 * Class for setting the template
 *
 * @package PeanutCMS
 */

/**
 * Templates class
 */
class Templates {

  /**
   * Current template as an array
   * @var array
   */
  var $template;

  /**
   * Template definitions
   * @var array
   */
  var $templates;

  /**
   * PHP5-style constructor
   */
  function __construct() {
    global $PEANUT;

    if (!$PEANUT['configuration']->exists('menu')) {
      $PEANUT['configuration']->set(
        'menu',
        array(
          'label'      => tr('Home'),
          'template'   => 'list-posts',
          'parameters' => array(
            'sortDesc' => 'date',
            'perPage'  => 10
          )
        ),
        array(
          'label'      => tr('Links'),
          'template'   => 'page',
          'parameters' => array('p' => 2)
        ),
        array(
          'label'      => tr('About'),
          'template'   => 'page',
          'parameters' => array('p' => 1)
        )
      );
    }

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
   * Set the template
   *
   * @param string $name Name of template
   * @param int $priority Priority; can this template be overridden
   * @param array $parameters Template parameters
   * @param int $status Optional HTTP status code
   * @return void
   */
  function setTemplate($name, $priority, $parameters = array(), $status = 200) {
    global $PEANUT;
    if (!empty($this->template) AND $priority <= $this->template['priority'])
      return;
//    $PEANUT['http']->setStatus($status);
    $this->template = array(
      'name' => $name,
      'priority' => $priority,
      'parameters' => $parameters,
      'status' => $status
    );
  }

  /**
   *
   */
  function setFinal() {
    global $PEANUT;
    $PEANUT['hooks']->run('setTemplate');
    // Show a 404-page if no other template has been set
    $this->setTemplate('404', 1, array(), 404);

    $PEANUT['http']->setStatus($this->template['status']);
    if (isset($this->template['parameters']) AND is_array($this->template['parameters']))
      $PEANUT['http']->params = array_merge($PEANUT['http']->params, $this->template['parameters']);
    $path = $this->getPath($this->template['name'], $PEANUT['http']->params);
    if (is_array($path) AND $path != $PEANUT['http']->path)
      $PEANUT['http']->redirectPath($path);
  }

  /**
   * Define a template
   *
   * Callback function should look like this:
   * getPath(string $template [, array $parameters = array()])
   *
   * @param string $name Name of template
   * @param callback $pathFunction Function that returns a proper path-array
   * @param callback $titleFunction Function that returns a page title
   */
  function defineTemplate($name, $pathFunction, $titleFunction = null) {
    if (!is_callable($pathFunction))
      return;
    $this->templates[$name] = array('pathFunction' => $pathFunction, 'titleFunction' => $titleFunction);
  }

  /**
   * Get path of current template
   */
  function getPath($template = null, $parameters = null) {
    global $PEANUT;
    if (!isset($template))
      $template = $this->template['name'];
    if (!isset($parameters))
      $parameters = $PEANUT['http']->params;

    if (empty($this->templates[$template]))
      return;
    if (!isset($this->templates[$template]['pathFunction']) OR !is_callable($this->templates[$template]['pathFunction']))
      return;
    $index = $PEANUT['configuration']->get('index');
    if ($template == $index['template'] AND
            ($parameters == $index['parameters'] OR
            array_intersect_assoc($parameters, $index['parameters']) == $index['parameters']))
      return array();
    $path = call_user_func($this->templates[$template]['pathFunction'], $template, $parameters);
    if (is_array($path))
      return $path;
    return array();
  }

  /**
   * Get title of current template
   */
  function getTitle($template = null, $parameters = null) {
    global $PEANUT;
    if (!isset($template)) {
      $template = $this->template['name'];
    }
    if (!isset($parameters)) {
      $parameters = $PEANUT['http']->params;
    }
    if (empty($this->templates[$template])) {
      return;
    }
    if (!isset($this->templates[$template]['titleFunction'])
        OR !is_callable($this->templates[$template]['titleFunction'])) {
      return;
    }
    return call_user_func(
      $this->templates[$template]['titleFunction'],
      $template, $parameters
    );
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
