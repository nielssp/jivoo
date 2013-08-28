<?php
/**
 * Base class for views
 * @package Core\Templates
 */
abstract class ViewBase {
  /**
   * @var array Associative array of data from controller
   */
  protected $data = array();

  /**
   * @var array Associative array of block names and content
   */
  private $blocks = array();

  /**
   * @var string Name of capturing block
   */
  private $currentBlock = null;

  /**
   * @var string Mode of block capturing, 'assign', 'prepend' or 'append'
   */
  private $blockMode = null;

  /**
   * @var string Content for parent template or layout
   */
  private $conent = '';

  /**
   * @var string Name of parent template
   */
  private $extend = null;

  /**
   * @var array Associative array of file names an provider arrays
   */
  private $providers = array();

  /**
   * @var array Associative array of file names and true-values for files
   * already inserted into page
   */
  private $inserted = array();
  
  /**
   * @var Dictionary Collection of modules
   */
  private $m = null;
  
  /**
   * @var Request Current request
   */
  private $request;

  /**
   * Constructor.
   * @param Templates $templates Templates module
   */
  public function __construct(Templates $templates, Routing $routing) {
    $this->m = new Dictionary();
    $this->m->Templates = $templates;
    $this->m->Routing = $routing;

    $this->request = $this->m->Routing->getRequest();

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
    return $this->data[$name];
  }

  /**
   * Set value of data variable
   * @param string $name Variable name
   * @param mixed $value Value
   */
  public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  /**
   * Check whether or not a variable is set
   * @param string $name Variable name
   */
  public function __isset($name) {
    return isset($this->data[$name]);
  }

  /**
   * Begin capturing output for block
   * @param string $block Block name
   */
  protected function begin($block) {
    $this->blockMode = 'assign';
    $this->content .= ob_get_clean();
    $this->currentBlock = $block;
    ob_start();
  }

  /**
   * Begin capturing output for block, append mode
   * @param string $block Block name
   */
  protected function append($block) {
    $this->blockMode = 'append';
    $this->content .= ob_get_clean();
    $this->currentBlock = $block;
    ob_start();
  }

  /**
   * Begin capturing output for block, prepend mode
   * @param string $block Block name
   */
  protected function prepend($block) {
    $this->blockMode = 'prepend';
    $this->content .= ob_get_clean();
    $this->currentBlock = $block;
    ob_start();
  }

  /**
   * End a capturing block
   */
  protected function end() {
    if (isset($this->currentBlock)) {
      if (!isset($this->blocks[$this->currentBlock])) {
        $this->blocks[$this->currentBlock] = '';
      }
      switch ($this->blockMode) {
        case 'append':
          $this->blocks[$this->currentBlock] .= ob_get_clean();
          break;
        case 'prepend':
          $this->blocks[$this->currentBlock] = ob_get_clean()
          . $this->blocks[$this->currentBlock];
          break;
        case 'assign':
        default:
          $this->blocks[$this->currentBlock] = ob_get_clean();
          break;
      }
      $this->currentBlock = null;
      ob_start();
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
   * Get a link to an asset
   * @param string $file Asset
   * @return string Absolute path to asset
   */
  protected function asset($file) {
    return $this->m->Templates->getAsset($file);
  }

  /**
   * Provide a file
   * @param string $file File name, e.g. 'jquery.js'
   * @param string $path Absolute path to file 
   * @param string[] $dependencies List of other files that must be inserted
   * before this one, e.g. 'jquery-ui.js' would depend on 'jquery.js' and
   * 'jquery-ui.css'
   */
  public function provide($file, $path, $dependencies = array()) {
    $this->providers[$file] = array(
      'path' => $path,
      'dependencies' => $dependencies,
    );
  }

  /**
   * Insert script or stylesheet into view
   * @param string $file File name
   * @throws Exception If unknown type
   */
  private function insert($file) {
    if (is_array($file)) {
      foreach ($file as $f) {
        $this->insert($f);
      }
      return;
    }
    if (isset($this->inserted[$file])) {
      return;
    }
    $this->inserted[$file] = true;
    $type = Utilities::getFileExtension($file);
    switch ($type) {
      case 'js':
        $path = $this->file('js/' . $file);
        $block = 'script';
        break;
      case 'css':
        $path = $this->file('css/' . $file);
        $block = 'style';
        break;
      default:
        throw new Exception(tr('Unknown type of file: %1', $type));
    }
    if (isset($this->providers[$file])) {
      $path = $this->providers[$file]['path'];
      foreach ($this->providers[$file]['dependencies'] as $dependency) {
        $this->insert($dependency);
      }
    }
    if ($block == 'script') {
      $this->appendTo(
        'script',
        '<script type="text/javascript" src="' . h($path) . '"></script>'
          . PHP_EOL
      );
    }
    else {
      $this->appendTo(
        'style',
        '<link rel="stylesheet" type="text/css" href="' . h($path) . '" />'
          . PHP_EOL
      );
    }
  }

  /**
   * Insert stylesheet into view, will look for file in 'assets/css' if not
   * provided by another source
   * @param string $style Stylesheet name, e.g. 'style.css'
   */
  public function style($style) {
    $this->insert($style);
  }

  /**
   * Insert script into view, will look for file in 'assets/js' if not
   * provided by another source
   * @param string $script Script name, e.g. 'script.js'
   */
  public function script($script) {
    $this->insert($script);
  }

  /**
   * Insert meta into view
   * @param string $name Meta name
   * @param string $content Meta content
   */
  public function meta($name, $content) {
    if (!isset($this->blocks['meta'])) {
      $this->blocks['meta'] = '';
    }
    $this->blocks['meta'] .=
    '<meta name="' . h($name) . '" content="' . h($content) . '" />'
      . PHP_EOL;
  }
  
  /**
   * Append value to a block
   * @param string $block Block name
   * @param string $value Value
   */
  public function appendTo($block, $value) {
    if (!isset($this->blocks[$block])) {
      $this->blocks[$block] = $value;
    }
    else {
      $this->blocks[$block] .= $value;
    }
  }

  /**
   * Prepend value to a block
   * @param string $block Block name
   * @param string $value Value
   */
  public function prependTo($block, $value) {
    if (!isset($this->blocks[$block])) {
      $this->blocks[$block] = $value;
    }
    else {
      $this->blocks[$block] = $value . $this->blocks[$block];
    }
  }

  /**
   * Assign a value to a block
   * @param string $block Block name
   * @param string $value Value
   */
  public function assign($block, $value) {
    $this->blocks[$block] = $value;
  }

  /**
   * Get value of a bock
   * @param string $block Block name
   * @param string $default Return value if block not set, default is the empty
   * string
   * @return string Value of block 
   */
  public function block($block, $default = '') {
    if (isset($this->blocks[$block])) {
      return $this->blocks[$block];
    }
    return $default;
  }

  /**
   * Check whether or not a block is empty
   * @param string $block block name
   * @return boolean True if not set, false otherwise
   */
  public function isEmpty($block) {
    return !isset($this->blocks[$block]);
  }

  /**
   * Extend another template, i.e. set parent template
   * @param string $template Template
   */
  protected function extend($template) {
    $this->extend = $template;
  }
  
  /**
   * Find template
   * @param string $template Template
   */
  protected function findTemplate($template) {
    
  }

  /**
   * Embed content another template into the current template
   * @param string $_template Template
   * @param string $_data Additional data for template
   */
  protected abstract function embed($_template, $_data = array());

  /**
   * Render template
   * @param string $template Template
   * @return string Rendered template
   */
  private function render($template) {
    ob_start();
    $this->content = '';
    $this->embed($template);
    if (isset($this->extend)) {
      $template = $this->extend;
      $this->extend = null;
      $this->content .= ob_get_clean();
      $this->assign('content', $this->content);
      return $this->render($template);
    }
    else if (isset($this->layout)) {
      $template = $this->layout;
      $this->layout = null;
      $this->content .= ob_get_clean();
      $this->assign('content', $this->content);
      return $this->render($template);
    }
    return ob_get_clean();
  }

  /**
   * Get template output
   * @param string $template Template
   * @return string Output of template
   */
  public function fetch($template) {
    return $this->render($template);
  }

  /**
   * Display a template
   * @param string $template Template
   */
  public function display($template) {
    echo $this->fetch($template);
  }
}