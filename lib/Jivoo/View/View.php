<?php
/**
 * 
 * @package Jivoo\View
 */
class View extends LoadableModule {
  
  protected $modules = array('Routing', 'Assets');
  
  private $data;
  
  private $extensions;
  
  private $blocks;
  
  private $template = null;
  
  private $functions = array();
  
  private $resources = null;
  
  private $templateDirs = array();
  
  protected function init() {
    $this->resources = new ViewResources($this->m->Assets);
    $this->extensions = new ViewExtensions($this);
    $this->data = new ViewData();
    $this->blocks = new ViewBlocks($this);
    
    $this->addTemplateDir($this->p('app', 'templates'));

    $this->addFunction('link', array($this->m->Routing, 'getLink'));
    $this->addFunction('url', array($this->m->Routing, 'getUrl'));
    $this->addFunction('isCurrent', array($this->m->Routing, 'isCurrent'));
    $this->addFunction('mergeRoutes', array($this->m->Routing, 'mergeRoutes'));
    $this->addFunction('file', array($this->m->Assets, 'getAsset'));

    $this->addFunctions(
      $this->blocks,
      array('icon', 'meta', 'relation', 'block', 'isEmpty')
    );
    $this->addFunctions(
      $this->resources,
      array('provide', 'import', 'resourceBlock', 'importConditional')
    );
    $this->addFunctions(
      $this->extensions,
      array('extensions')
    );
  }
  
  public function __get($property) {
    switch ($property) {
      case 'data':
      case 'blocks':
      case 'resources':
      case 'template':
      case 'extensions':
        return $this->$property;
    }
    return parent::__get($property);
  }

  public function __call($function, $parameters) {
    if (isset($this->functions[$function]))
      return call_user_func_array($this->functions[$function], $parameters);
    return parent::__call($function, $parameters);
  }
  
  public function addFunction($name, $callback) {
    $this->functions[$name] = $callback;
  }
  
  public function addFunctions($object, $methods) {
    foreach ($methods as $method)
      $this->functions[$method] = array($object, $method);
  }

  
  /**
   * Add a template directory
   * @param string $dir Absolute path to directory
   * @param int $priority Priority
   */
  public function addTemplateDir($dir, $priority = 5) {
    $this->templateDirs[$dir] = $priority;
  }
  
  /**
   * Find template in available template directories
   * @param string $template Template
   * @return string|false $path Absolute path to template or false if not found
   */
  public function findTemplate($template) {
    if (file_exists($template))
      return $template;
    foreach ($this->templateDirs as $dir => $priority) {
      if (substr($dir, -1, 1) != '/') {
        $dir .= '/';
      }
      $path = $dir . $template . '.php';
      if (file_exists($path)) {
        return $path;
      }
    }
    return false;
  }
  
  public function render($template, $data = array()) {
    if (isset($this->template))
      return $this->template->render($template, $data);
    arsort($this->templateDirs);
    $this->data->flash = $this->request->session->flash;
    $this->template = new Template($this);
    $result = $this->template->render($template, $data);
    $this->template = null;
    return $result;
  }
  
  public function renderOnly($template, $data = array()) {
    if (isset($this->template))
      return $this->template->render($template, $data);
    arsort($this->templateDirs);
    $this->data->flash = $this->request->session->flash;
    $this->template = new Template($this);
    $this->template->ignoreExtend(true);
    $result = $this->template->render($template, $data);
    $this->template = null;
    return $result;
  }
  
}
