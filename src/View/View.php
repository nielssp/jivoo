<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Utilities;
use Jivoo\View\Compile\TemplateCompiler;

/**
 * The view module.
 * @property-read ViewData $data View data.
 * @property-read ViewResource $resources View resources.
 * @property-read ViewExtensions $extensions View extensions.
 * @property-read Template|null $template Template system if it has been
 * initialized.
 * @property-read ViewBlocks $blocks View blocks.
 * @property-read TemplateCompiler|null $compiler The template compiler if enabled. 
 * @method string link(array|Linkable|string|null $route = null) Alias for
 * {@see Routing::getLink}. 
 * @method string url(array|Linkable|string|null $route = null) Alias for
 * {@see Routing::getUrl}. 
 * @method bool isCurrent(array|Linkable|string|null $route = null)
 *  Alias for {@see Routing::isCurrent}.
 * @method array mergeRoutes(array|Linkable|string|null $route = null,
 *       array $mergeWith = array()) Alias for {@see Routing::mergeRoutes}.
 * @method string file(string $file) Alias for {@see Assets::getAsset}.
 * @method icon(string $icon) Alias for {@see ViewBlocks::icon}.
 * @method meta(string $name, string $content) Alias for {@see ViewBlocks::meta}.
 * @method relation(string $rel, string $type, string $href) Alias for {@see ViewBlocks::relation}.
 * @method string block(string $name, string $default = '') Alias for {@see ViewBlocks::block}.
 * @method bool isEmpty(string $block) Alias for {@see ViewBlocks::isEmpty}.
 * @method provide(string $resource, string $location, string[] $dependencies = array(), string $condition = null)
 *  Alias for {@see ViewResources::provide}.
 * @method import(string $resource, string $resources,...) Alias for {@see ViewResources::import}.
 * @method openFrame() Alias for {@see ViewResources::openFrame}.
 * @method closeFrame() Alias for {@see ViewResources::closeFrame}.
 * @method importConditional(string $resource, string $condition)
 *  Alias for {@see ViewResources::importConditional}.
 * @method string resourceBlock() Alias for {@see ViewResources::resourceBlock}.
 * @method ViewExtension[] extensions(string $hook = null, string $type = 'ViewExtension')
 *  Alias for {@see ViewExtensions::extensions}.
 */
class View extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Routing', 'Assets');
  
  /**
   * @var ViewData Data for view.
   */
  private $data;
  
  /**
   * @var ViewExtensions Collection of view extensions.
   */
  private $extensions;
  
  /**
   * @var ViewBlocks Collection of view blocks.
   */
  private $blocks;
  
  /**
   * @var Template Template system.
   */
  private $template = null;
  
  /**
   * @var callback[] Associative array mapping function names to callbacks.
   */
  private $functions = array();
  
  /**
   * @var ViewResources Collection of view resources.
   */
  private $resources = null;
  
  /**
   * @var int[] Associative array mapping paths to priorities.
   */
  private $templateDirs = array();
  
  /**
   * @var TemplateCompiler Template compiler if enabled.
   */
  private $compiler = null;
  
  /**
   * @var bool Whether or not to automatically compile templates.
   */
  private $autoCompile = false;
  
  /**
   * @var string[]
   */
  private $compiled = array();
  
  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->resources = new ViewResources($this->m->Assets);
    $this->extensions = new ViewExtensions($this);
    $this->data = new ViewData();
    $this->blocks = new ViewBlocks($this);
    
    if ($this->config->get('compileTemplates', false)) {
      $this->vendor->import('jivoo/simplehtmldom');
      $this->compiler = new TemplateCompiler();
      $this->autoCompile = true;
    }
    
    $this->data->app = $this->app->manifest;

    $this->addTemplateDir('Core', 'templates', 4);
    $this->addTemplateDir('app', 'templates');

    $this->addFunction('link', array($this->m->Routing, 'getLink'));
    $this->addFunction('url', array($this->m->Routing, 'getUrl'));
    $this->addFunction('isCurrent', array($this->m->Routing, 'isCurrent'));
    $this->addFunction('mergeRoutes', array($this->m->Routing, 'mergeRoutes'));
    $this->addFunction('file', array($this->m->Assets, 'getAsset'));

    $this->addFunctions(
      $this->blocks,
      array('icon', 'meta', 'relation', 'block', 'isEmpty')
    );
    $this->addFunctions($this->resources, 
      array('provide', 'import', 'resourceBlock', 'importConditional', 
        'openFrame', 'closeFrame')
    );
    $this->addFunctions(
      $this->extensions,
      array('extensions')
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'data':
      case 'blocks':
      case 'resources':
      case 'template':
      case 'extensions':
      case 'compiler':
        return $this->$property;
    }
    return parent::__get($property);
  }

  /**
   * {@inheritdoc}
   */
  public function __call($function, $parameters) {
    if (isset($this->functions[$function]))
      return call_user_func_array($this->functions[$function], $parameters);
    return parent::__call($function, $parameters);
  }
  
  /**
   * Add a view function. Function can be called from templates.
   * @param string $name Function name.
   * @param callback $callback Callback for function.
   */
  public function addFunction($name, $callback) {
    $this->functions[$name] = $callback;
  }
  
  /**
   * Add several methods of an object as view functions.
   * @param object $object An object.
   * @param string[] $methods List of method names.
   */
  public function addFunctions($object, $methods) {
    foreach ($methods as $method)
      $this->functions[$method] = array($object, $method);
  }
  
  /**
   * Add a template directory.
   * @param string $key Path key.
   * @param string $path Path.
   * @param int $priority Priority.
   */
  public function addTemplateDir($key, $path, $priority = 5) {
    $dir = $this->p($key, $path);
    $this->templateDirs[$dir] = array(
      'key' => $key,
      'path' => $path,
      'init' => false, 
      'priority' => $priority
    );
  }

  /**
   * Find the 'init.php'-template in a template directory if the directory has
   * not been initialized. 
   * @param string $key Path key.
   * @param string $path Path.
   * @return string|null Absolute path to init-file or null if the file does
   * not exist or the file has already been included.
   */
  public function getInitFile($key, $path) {
    $dir = $this->p($key, $path);
    if (!isset($this->templateDirs[$dir]) or $this->templateDirs[$dir]['init'])
      return;
    $this->templateDirs[$dir]['init'] = true;
    if (substr($dir, -1, 1) != '/')
      $dir .= '/';
    $file = $dir . 'init.php';
    if (file_exists($file))
      return realpath($file);
    return null;
  }
  
  /**
   * Compile an HTML template.
   * @param string $dir Template dir.
   * @param string $template Absolute path to template.
   * @return string Absolute path to compiled template.
   * @throws InvalidTemplateException If template could not be compiled.
   */
  public function compileTemplate($dir, $template) {
    if (!isset($this->compiled[$template])) {
      $source = $dir . $template;
      $compiled = $dir . 'compiled/' . $template . '.php';
      $dir = dirname($compiled);
      if (!Utilities::dirExists($dir)) {
        throw new InvalidTemplateException(tr('Could not create directory: %1', $dir));
      }
      $output = $this->compiler->compile($source);
      $file = fopen($compiled, 'w');
      if (!$file)
        throw new InvalidTemplateException(tr('Could not write compiled template: %1', $compiled));
      $this->logger->info(tr('Compiling template %1', $source));
      fwrite($file, $output);
      fclose($file);
      $this->compiled[$template] = $compiled;
    }
    return $this->compiled[$template];
  }
  
  /**
   * Find a template in available template directories.
   * @param string $name Template name.
   * @return array|null An associative array or null if template not found.
   * The associative array is of the form:
   * <code>
   * array(
   *   'key' => ..., // Path key for template directory (e.g. 'app').
   *   'path' => ..., // Path for template directory.
   *   'init' => ..., // Whether the init.php-file in the template directory (bool)
   *   'priority' => ..., // Priority of template directory (int)
   *   'compiled' => ..., // Whether this is a compiled template (bool)
   *   'name' => ..., // Template name, i.e. the function parameter
   *   'file' => ..., // Absolute path to template 
   * );
   * </code>
   */
  public function findTemplate($name) {
    if (Utilities::isAbsolutePath($name)) {
      return array(
        'compiled' => false,
        'name' => $name,
        'file' => $name
      );
    }
    $result = array(); 
    foreach ($this->templateDirs as $dir => $templateDir) {
      $result = $templateDir;
      if (substr($dir, -1, 1) != '/')
        $dir .= '/';
      if ($this->autoCompile) {
        if (file_exists($dir . $name)) {
          if (Utilities::getFileExtension($name) === 'html') {
             try {
               $result['file'] = $this->compileTemplate($dir, $name);
               $result['compiled'] = true;
               break;
             }
             catch (InvalidTemplateException $e) {
               trigger_error($e->getMessage(), E_USER_WARNING);
             }
          }
        }
      }
      $file = $dir . 'compiled/' . $name . '.php';
      if (file_exists($file)) {
        $result['file'] = $file;
         $result['compiled'] = true;
        break;
      }
      $file = $dir . $name . '.php';
      if (file_exists($file)) {
        $result['file'] = $file;
        $result['compiled'] = false;
        break;
      }
    }
    if (!isset($result['file']))
      return null;
    $result['name'] = $name;
    return $result;
  }
  
  /**
   * Find layout template for a template.
   * @param string $template Template name.
   * @return string|null Name of layout template or null if not found.
   */
  public function findLayout($template) {
    if (Utilities::isAbsolutePath($template))
      return null;
    $extension = Utilities::getFileExtension($template);
    $dir = $template;
    do {
      $dir = dirname($dir);
      if ($dir === '.')
        $template = 'layout.' . $extension;
      else
        $template = $dir . '/layout.' . $extension;
      $file = $this->findTemplate($template);
      if (isset($file))
        return $template;
    } while ($dir != '.');
    return null;
  }
  
  /**
   * Render a template.
   * @param string $template Template name.
   * @param array $data Addtional data for template.
   * @param bool $withLayout Whether or not to render the layout.
   * @return string Content of template.
   */
  public function render($template, $data = array(), $withLayout = true) {
    if (isset($this->template))
      return $this->template->render($template, $data, $withLayout);
    uasort($this->templateDirs, array('Jivoo\Core\Utilities', 'prioritySorter'));
    $this->template = new Template($this);
    $result = $this->template->render($template, $data, $withLayout);
    $this->template = null;
    return $result;
  }
  
  /**
   * Render a template without layout or parent templates.
   * @param string $template Template name.
   * @param array $data Additional data for template.
   * @return string Content of template.
   */
  public function renderOnly($template, $data = array()) {
    return $this->render($template, $data, false);
  }
  
}
