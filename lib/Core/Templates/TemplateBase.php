<?php
/**
 * Abstract template class
 * @package Core\Templates
 */
abstract class TemplateBase {
  /**
   * @var Dictionary Collection of modules
   */
  private $m = null;

  /**
   * @var Controller Associated controller
   */
  private $controller = null;

  /**
   * @var string[] List of template directories
   */
  private $templatePaths = array();

  /**
   * @var Request Current request
   */
  private $request;

  /**
   * @var array Associative array of data from controller
   */
  protected $data = array();

  /**
   * Constructor.
   * @param Templates $templates Templates module
   * @param Routing $routing Routing module
   * @param Controller $controller Associated controller 
   */
  public final function __construct(Templates $templates, Routing $routing,
                                    $controller = null) {
    $this->m = new Dictionary();
    $this->m->Templates = $templates;
    $this->m->Routing = $routing;

    $this->request = $this->m->Routing->getRequest();

    $this->controller = $controller;

    $this->data['messages'] = $this->request->session->messages;
    $this->data['alerts'] = $this->request->session->alerts;
    $this->data['notices'] = $this->request->session->notices;
  }

  /**
   * Get value of data variable
   * @param string $name Variable name
   * @return mixed Value
   */
  public function __get($name) {
    return $this->get($name);
  }

  /**
   * Set value of data variable
   * @param string $name Variable name
   * @param mixed $value Value
   */
  public function __set($name, $value) {
    $this->set($name, $value);
  }

  /**
   * Get value of data variable
   * @param string $name Variable name
   * @return mixed Value
   */
  public function get($name) {
    if (isset($this->data[$name])) {
      return $this->data[$name];
    }
    return null;
  }

  /**
   * Set value of data variable. Or if $name is an array, set multiple data
   * variables.
   * @param string|array $name Variable name or associative array of variable
   * names and values
   * @param mixed $value Value
   */
  public function set($name, $value = null) {
    if (is_array($name)) {
      foreach ($name as $n => $value) {
        $this->set($n, $value);
      }
    }
    else {
      $this->data[$name] = $value;
    }
  }

  /**
   * Convert a route to a URL
   * @param array|ILinkable|string|null $route A route, see {@see Routing}
   * @return string URl
   */
  protected function link($route = null) {
    return $this->m->Routing->getLink($route);
  }

  /**
   * Check whether or not a route is the current route
   * @param array|ILinkable|string|null $route A route, see {@see Routing}
   * @return bool True if current, fals otherwise
   */
  protected function isCurrent($route = null) {
    return $this->m->Routing->isCurrent($route);
  }

  /**
   * Get URL for a file (asset).
   * @param string $file File path relative to assets directory
   */
  protected function file($file) {
    return $this->m->Templates->getFile($file);
  }

  /**
   * Add a script include to HTML collection
   * @param string $id Identifier
   * @param string $file File path
   * @param string[] $dependencies List of script/style dependencies
   */
  protected function addScript($id, $file, $dependencies = array()) {
    $this->m->Templates->addScript($id, $file, $dependencies);
  }

  /**
   * Add a stylesheet include to HTML collection
   * @param string $id Identifier
   * @param string $file File path
   * @param string[] $dependencies List of script/style dependencies
   */
  protected function addStyle($id, $file, $dependencies = array()) {
    $this->m->Templates->addStyle($id, $file, $dependencies);
  }

  /**
   * Insert a script into page
   * @param string $id Identifier
   * @param string $file File path
   * @param string[] $dependencies List of script/style dependencies
   */
  protected function insertScript($id, $file, $dependencies = array()) {
    $this->m->Templates->insertScript($id, $file, $dependencies);
  }

  /**
   * Insert a stylesheet into page
   * @param string $id Identifier
   * @param string $file File path
   * @param string[] $dependencies List of script/style dependencies
   */
  protected function insertStyle($id, $file, $dependencies = array()) {
    $this->m->Templates->insertStyle($id, $file, $dependencies);
  }
  
  /**
   * Insert a meta tag into page
   * @param string $name Meta name
   * @param string $content Meta content
   */
  protected function insertMeta($name, $content) {
    $this->m->Templates->insertMeta($name, $content);
  }
  
  /**
   * Request a script
   * @param string$id Identifier
   */
  protected function requestScript($id) {
    return $this->m->Templates->requestHtml($id);
  }
  
  /**
   * Request a stylesheet
   * @param string$id Identifier
   */
  protected function requestStyle($id) {
    return $this->m->Templates->requestHtml($id);
  }

  /**
   * Set indentation for output (scripts, stylesheets, meta etc.)
   * @param int $indentation Number of spaces before each line
   */
  protected function setIndent($indentation = 0) {
    $this->m->Templates->setHtmlIndent($indentation);
  }

  /**
   * Output HTML for a specific location on the page
   * @param string $location Location identifier
   * @param string $linePrefix String to prefix each line with
   */
  protected function output($location, $linePrefix = '') {
    $this->m->Templates->outputHtml($location, $linePrefix);
  }

  /**
   * Set template paths searched by template
   * @param string[] $paths List of paths
   */
  public function setTemplatePaths($paths) {
    $this->templatePaths = $paths;
  }

  /**
   * Get template by name
   * @param string $template Template name
   * @param bool $return Whether or not to return content instead of
   * outputting
   */
  protected function getTemplate($template, $return = false) {
    return $this->m->Templates
      ->getTemplate($template, $this->templatePaths, $return);
  }

  /**
   * Get data associated with a template
   * @param string $template Template name
   */
  protected function getTemplateData($template) {
    return $this->m->Templates->getTemplateData($template);
  }

  /**
   * Render template
   * @param string $template Template 
   * @param bool $return Whether or not to return content instead of
   * outputting
   */
  public abstract function render($template, $return = false);

}
