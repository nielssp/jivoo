<?php
// Module
// Name           : Templates
// Version        : 0.2.0
// Description    : The PeanutCMS template system
// Author         : PeanutCMS
// Dependencies   : errors configuration http

/**
 * Class for setting the template
 *
 * @package PeanutCMS
 */

/**
 * Templates class
 */
class Templates extends ModuleBase {

  private $theme;

  private $html = array();

  private $availableHtml = array();

  private $indentation = 0;

  private $contentTypeSet = FALSE;

  private $prevParameters = array();

  private $parameters = array();

  private $hideLevel = HIDE_LEVEL;

  public function hideVersion() {
    return $this->hideLevel > 0;
  }

  public function hideIdentity() {
    return $this->hideLevel > 1;
  }

  protected function init() {
    $this->m->Configuration->setDefault(array(
      'site.title' => 'PeanutCMS',
      'site.subtitle' => 'The domesticated peanut is an amphidiploid or allotetraploid.'
    ));

    $this->setTheme(TEMPLATES);

    if ($this->m->Configuration->exists('site.meta')) {
      $meta = $this->m->Configuration->get('site.meta');
      if (is_array($meta)) {
        foreach ($meta as $name => $content) {
          $this->insertMeta($name, $content);
        }
      }
    }

    if ($this->m->Configuration->exists('system.hide')) {
      $hide = $this->m->Configuration->get('system.hide');
      if ($hide['identity'] == 'on') {
        $this->hideLevel = 2;
      }
      else if ($hide['version'] == 'on' AND $this->hideLevel < 1) {
        $this->hideLevel = 1;
      }
    }
  }

  /** @todo Move to Http module !! */
  private function setContentType($name) {
    if ($this->contentTypeSet) {
      return;
    }
    $fileName = explode('.', $name);
    $fileExt = $fileName[count($fileName) - 1];
    $contentType = NULL;
    switch ($fileExt) {
      case 'html':
      case 'htm':
        $contentType = "text/html";
        $this->insertHtml(
          'meta-charset', 'head-meta', 'meta',
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
      case 'json':
        $contentType = "application/json";
        break;
      case 'rss':
        $contentType = "application/rss+xml";
        break;
      case 'xml':
        $contentType = "application/xml";
        break;
      default:
        throw new Exception(tr('Unsupported content type: %1', $fileExt));
    }
    header('Content-Type:' . $contentType . ';charset=utf-8');
    $this->contentTypeSet = TRUE;
  }

  public function addScript($id, $file, $dependencies = array()) {
    $this->addHtml(
      $id, 'head-scripts', 'script',
      array(
        'type' => 'text/javascript',
        'src' => $file
      ), '', 10, $dependencies
    );
  }

  public function addStyle($id, $file, $dependencies = array()) {
    $this->addHtml(
      $id, 'head-styles', 'link',
      array(
        'rel' => 'stylesheet',
        'type' => 'text/css',
        'href' => $file
      ), '', 20, $dependencies
    );
  }

  public function insertScript($id, $file, $dependencies = array()) {
    $this->insertHtml(
      $id, 'head-scripts', 'script',
      array(
        'type' => 'text/javascript',
        'src' => $file
      ), '', 10, $dependencies
    );
  }

  public function insertStyle($id, $file, $dependencies = array()) {
    $this->insertHtml(
      $id, 'head-styles', 'link',
      array(
        'rel' => 'stylesheet',
        'type' => 'text/css',
        'href' => $file
      ), '', 20, $dependencies
    );
  }

  public function insertMeta($name, $content) {
    $this->insertHtml(
      $name, 'head-meta', 'meta',
      array(
        'name' => $name,
        'content' => $content
      )
    );
  }

  public function addHtml($id, $location, $tag, $parameters, $innerhtml = '', $priority = 5, $dependencies = array()) {
    $tag = strtolower($tag);
    if ($tag == 'script' AND !isset($parameters['type'])) {
      $parameters['type'] = 'text/javascript';
    }
    if ($tag == 'style' AND !isset($parameters['type'])) {
      $parameters['type'] = 'text/css';
    }
    $this->availableHtml[$id] = array(
      'tag' => $tag,
      'location' => $location,
      'innerhtml' => $innerhtml,
      'priority' => $priority,
      'parameters' => $parameters,
      'dependencies' => $dependencies
    );
  }

  public function requestHtml($id, $priority = 0) {
    if (isset($this->availableHtml[$id])) {
      $location = $this->availableHtml[$id]['location'];
      if (!isset($this->html[$location][$id])) {
        foreach ($this->availableHtml[$id]['dependencies'] as $dependency) {
          $request = $this->requestHtml($dependency, $priority + 1);
          if (is_int($request) AND $request <= $priority) {
//            echo "old:$priority;new:$request - 1;";
            $priority = $request - 1;
          }
        }
        $this->html[$location][$id] = array(
          'priority' => $priority
        );
      }
      return $this->html[$location][$id]['priority'];
    }
    return FALSE;
  }

  /**
  * Insert an HTML-tag (e.g. a script, stylesheet, meta-tag etc.) on the page
  *
  * @param string $id A uniqie identifier
  * @param string $location Location on page (e.g. 'head-top', 'head-bottom', 'body-top' or 'body-bottom')
  * @param string $tag HTML-tag (e.g. 'meta', 'link', 'script', 'style' etc.)
  * @param array $parameters HTML-parameters (e.g. array('src' => 'somescript.js'))
  * @param string $innerhtml Optional string to be placed between start- and end-tag
  * @param int $priority A high-priority (e.g. 10) tag will be inserted before a low-priority one (e.g 2)
  */
  public function insertHtml($id, $location, $tag, $parameters, $innerhtml = '', $priority = 5, $dependencies = array()) {
    $tag = strtolower($tag);
    if ($tag == 'script' AND !isset($parameters['type'])) {
      $parameters['type'] = 'text/javascript';
    }
    if ($tag == 'style' AND !isset($parameters['type'])) {
      $parameters['type'] = 'text/css';
    }
    $this->addHtml($id, $location, $tag, $parameters, $innerhtml, $priority, $dependencies);
    $this->requestHtml($id);
  }

  public function setHtmlIndent($indentation = 0) {
    $this->indentation = $indentation;
  }

  /**
   * Output HTML-code attached to a location on the page
   *
   * @param string $location Location on page (e.g. 'head-top', 'head-bottom', 'body-top' or 'body-bottom')
   */
  public function outputHtml($location, $linePrefix = '') {
    if (!isset($this->html[$location]) OR !is_array($this->html[$location])) {
      return;
    }
    uasort($this->html[$location], 'prioritySorter');
    foreach ($this->html[$location] as $id => $shown) {
      $html = $this->availableHtml[$id];
      echo str_repeat(' ', $this->indentation) . '<' . $html['tag'];
      foreach ($html['parameters'] as $parameter => $value) {
        echo ' ' . $parameter . '="' . addslashes($value) . '"';
      }
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

  public function setTheme($themeDir) {
    $this->theme = $themeDir;
  }

  /**
  * Return a link to a file in the current theme
  *
  * @param string $file File name
  * @return string Link
  */
  public function getFile($file) {
    if (isset($this->theme) AND file_exists(p($this->theme . $file))) {
      return w($this->theme . $file);
    }
    if (file_exists(p(PUB . $file))) {
      return w(PUB . $file);
    }
  }
  
  public function link($label, $controller = NULL, $action = 'index', $parameters = array()) {
    return $this->Core->Routes->getLink($controller, $action, $parameters);
  }

  public function set($name, $value, $template = '*') {
    if (!isset($this->parameters[$template])) {
      $this->parameters[$template] = array();
    }
    $this->parameters[$template][$name] = $value;
  }

  public function getTemplate($name, $additionalPaths = array()) {
    if (file_exists(p($this->theme . 'templates/' . $name. '.php'))) {
      $this->setContentType($name);
      return p($this->theme . 'templates/' . $name . '.php');
    }
    else if (file_exists(p(TEMPLATES . $name . '.php'))) {
      $this->setContentType($name);
      return p(TEMPLATES . $name . '.php');
    }
    else {
      foreach ($additionalPaths as $path) {
        if (substr($path, -1, 1) != '/') {
          $path .= '/';
        }
        if (file_exists($path . $name . '.php')) {
          $this->setContentType($name);
          return $path . $name . '.php';
        }
      }
      if (strpos($name, '.') === false) {
        return $this->getTemplate($name . '.html');
      }
    }
    return FALSE;
  }

  public function getTemplateData($name) {
    $data = array();
    if (isset($this->parameters['*'])) {
      $data = array_merge($data, $this->parameters['*']);
    }
    if (isset($this->parameters[$name])) {
      $data = array_merge($data, $this->parameters[$name]);
    }
    $data['site'] = $this->m->Configuration->get('site');
    return $data;
  }

  /**
   * @deprecated Replaced by the Template/class
   */
  public function renderTemplate($name, $parameters = array()) {
    $template = new Template($this, $this->Core->Routes);
    $template->set($parameters);
    $template->render($name);
  }

}