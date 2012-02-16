<?php
/*
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

    // Set default settings
    if (!$PEANUT['configuration']->exists('index'))
      $PEANUT['configuration']->set('index', array('template' => 'list-posts',
          'parameters' => array('sortDesc' => 'date','perPage' => 10)));
    
    if (!$PEANUT['configuration']->exists('menu'))
      $PEANUT['configuration']->set('menu', array(
          array('label' => tr('Home'), 'template' => 'list-posts',
          'parameters' => array('sortDesc' => 'date','perPage' => 10)),
          array('label' => tr('Links'), 'template' => 'page',
          'parameters' => array('p' => 2)),
          array('label' => tr('About'), 'template' => 'page',
          'parameters' => array('p' => 1))));
    
    
    if (count($PEANUT['http']->path) < 1) {
      $index = $PEANUT['configuration']->get('index');
      if (isset($index['template']))
        $this->setTemplate($index['template'], 5, $index['parameters']);
//      if (isset($index['parameters']) AND is_array($index['parameters'])) {
//        $refresh = false;
//        foreach ($index['parameters'] as $key => $value) {
//          if (isset($PEANUT['http']->params[$key]) AND $PEANUT['http']->params[$key] == $value) {
//            $refresh = true;
//            unset($PEANUT['http']->params[$key]);
//          }
//        }
//        if ($refresh)
//          $PEANUT['http']->refreshPath();
//        $PEANUT['http']->params = array_merge($index['parameters'], $PEANUT['http']->params);
//      }
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
    $this->template = array('name' => $name,
                            'priority' => $priority,
                            'parameters' => $parameters,
                            'status' => $status);
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
    if (!isset($template))
      $template = $this->template['name'];
    if (!isset($parameters))
      $parameters = $PEANUT['http']->params;
    if (empty($this->templates[$template]))
      return;
    if (!isset($this->templates[$template]['titleFunction']) OR !is_callable($this->templates[$template]['titleFunction']))
      return;
    return call_user_func($this->templates[$template]['titleFunction'], $template, $parameters);
  }

  
}