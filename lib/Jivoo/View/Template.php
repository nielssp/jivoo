<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View;

/**
 * Template system.
 * @package Jivoo\View
 * @method string link(array|ILinkable|string|null $route = null) Alias for
 * {@see Routing::getLink}. 
 * @method string url(array|ILinkable|string|null $route = null) Alias for
 * {@see Routing::getUrl}. 
 * @method bool isCurrent(array|ILinkable|string|null $route = null,
 *       string $defaultAction = 'index', array $defaultParameters = array())
 *  Alias for {@see Routing::isCurrent}.
 * @method array mergeRoutes(array|ILinkable|string|null $route = null,
 *       array $mergeWith = array()) Alias for {@see Routing::mergeRoutes}.
 * @method string file(string $file) Alias for {@see Assets::getAsset}.
 * @method icon(string $icon) Alias for {@see ViewBlocks::icon}.
 * @method meta(string $name, string $content) Alias for {@see ViewBlocks::meta}.
 * @method relation(string $rel, string $type, string $href) Alias for {@see ViewBlocks::relation}.
 * @method string block(string $name, string $default = '') Alias for {@see ViewBlocks::block}.
 * @method bool isEmpty(string $block) Alias for {@see ViewBlocks::isEmpty}.
 * @method provide(string $resource, string $location, string[] $dependencies = array(), string $condition = null)
 *  Alias for {@see ViewResources::provide}.
 * @method import(string $resource) Alias for {@see ViewResources::import}.
 * @method importConditional(string $resource, string $condition)
 *  Alias for {@see ViewResources::importConditional}.
 * @method string resourceBlock() Alias for {@see ViewResources::resourceBlock}.
 * @method IViewExtension[] extensions(string $hook = null, string $type = 'IViewExtension')
 *  Alias for {@see ViewExtensions::extensions}.
 */
class Template {
  /**
   * @var string Name of capturing block.
   */
  private $currentBlock = null;

  /**
   * @var string Mode of block capturing, 'assign', 'prepend' or 'append'.
   */
  private $blockMode = null;

  /**
   * @var string Content for parent template or layout.
   */
  private $content = '';
  
  /**
   * @var string Name of parent template.
   */
  private $extend = null;
  
  /**
   * @var string[] Stack of embedded templates.
   */
  private $templateStack = array();
  
  /**
   * @var bool Whether or not to ignore extends.
   */
  private $ignoreExtend = false;

  /**
   * @var View View.
   */
  private $view;
  
  /**
   * Construct template handler.
   * @param View $view The view.
   */
  public function __construct(View $view) {
    $this->view = $view;
  }
  
  /**
   * Set whether or not to ignore extends.
   * @param bool $ignore Ignore extends. 
   */
  public function ignoreExtend($ignore) {
    $this->ignoreExtend = $ignore;
  } 
  
  /**
   * Get name of current template if any.
   * @return string|null Name of current template, or null if not in a template.
   */
  public function getCurrent() {
    if (isset($this->templateStack[0]))
      return $this->templateStack[0];
    return null;
  }
  
  /**
   * Extend another template, i.e. set parent template.
   * @param string $template Template name.
   */
  protected function extend($template) {
    $this->extend = $template;
  }
  
  /**
   * Embed another template into the current template.
   * @param string $_template Name of template.
   * @param array $_data Additional data for template.
   * @throws TemplateNotFoundException If template could not be found.
   */
  protected function embed($_template, $_data = array()) {
    extract($_data, EXTR_SKIP);
    extract($this->view->data->toArray(), EXTR_SKIP);
    extract($this->view->data[$_template]->toArray(), EXTR_SKIP);
    $_file = $this->view->findTemplate($_template);
    if ($_file === false) {
      throw new TemplateNotFoundException(tr('Template not found: %1', $_template));
    }
    array_unshift($this->templateStack, $_template);
    require $_file;
    array_shift($this->templateStack);
  }

  /**
   * Render template.
   * @param string $template Template name.
   * @return string Rendered template, e.g. HTML code for HTML templates.
   */
  public function render($template, $data = array()) {
    ob_start();
    $extend = $this->extend;
    $this->extend = null;
    $this->content = '';
    $this->embed($template, $data);
    if (isset($this->extend)) {
      $template = $this->extend;
      $this->extend = null;
      if (!$this->ignoreExtend) {
        $this->content .= ob_get_clean();
        $this->view->blocks->assign('content', $this->content);
        return $this->render($template, $data);
      }
    }
    $this->extend = $extend;
    return $this->content . ob_get_clean();
  }
  
  /**
   * Call a view function.
   * @param string $function View function name.
   * @param array $parameters Parameters for function.
   * @return mixed Output of function.
   */
  public function __call($function, $parameters) {
    return $this->view->__call($function, $parameters);
  }
  
  /**
   * Begin capturing output for block.
   * @param string $block Block name.
   */
  public function begin($block) {
    $this->blockMode = 'assign';
    $this->content .= ob_get_clean();
    $this->currentBlock = $block;
    ob_start();
  }

  /**
   * Begin capturing output for block, append mode.
   * @param string $block Block name.
   */
  public function append($block) {
    $this->blockMode = 'append';
    $this->content .= ob_get_clean();
    $this->currentBlock = $block;
    ob_start();
  }

  /**
   * Begin capturing output for block, prepend mode.
   * @param string $block Block name.
   */
  public function prepend($block) {
    $this->blockMode = 'prepend';
    $this->content .= ob_get_clean();
    $this->currentBlock = $block;
    ob_start();
  }

  /**
   * End a capturing block.
   */
  public function end() {
    if (isset($this->currentBlock)) {
      if (!isset($this->blocks[$this->currentBlock])) {
        $this->blocks[$this->currentBlock] = '';
      }
      call_user_func(
        array($this->view->blocks, $this->blockMode),
        $this->currentBlock,
        ob_get_clean()
      );
      $this->currentBlock = null;
      ob_start();
    }
  }
}

/**
 * When a template cannout be found.
 * @package Jivoo\View
 */
class TemplateNotFoundException extends \Exception { }