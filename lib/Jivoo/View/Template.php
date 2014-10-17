<?php
class Template {

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
  private $content = '';
  
  /**
   * @var string Name of parent template
   */
  private $extend = null;
  
  private $templateStack = array();
  
  /**
   * @var bool Whether or not to ignore extends
   */
  private $ignoreExtend = false;

  private $view;
  
  public function __construct(View $view) {
    $this->view = $view;
  }
  
  public function ignoreExtend($ignore) {
    $this->ignoreExtend = $ignore;
  } 
  
  public function getCurrent() {
    if (isset($this->templateStack[0]))
      return $this->templateStack[0];
    return null;
  }
  
  /**
   * Extend another template, i.e. set parent template
   * @param string $template Template
   */
  protected function extend($template) {
    $this->extend = $template;
  }
  
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
   * Render template
   * @param string $template Template
   * @return string Rendered template
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
  
  public function __call($function, $parameters) {
    return $this->view->__call($function, $parameters);
  }
  
  /**
   * Begin capturing output for block
   * @param string $block Block name
   */
  public function begin($block) {
    $this->blockMode = 'assign';
    $this->content .= ob_get_clean();
    $this->currentBlock = $block;
    ob_start();
  }

  /**
   * Begin capturing output for block, append mode
   * @param string $block Block name
   */
  public function append($block) {
    $this->blockMode = 'append';
    $this->content .= ob_get_clean();
    $this->currentBlock = $block;
    ob_start();
  }

  /**
   * Begin capturing output for block, prepend mode
   * @param string $block Block name
   */
  public function prepend($block) {
    $this->blockMode = 'prepend';
    $this->content .= ob_get_clean();
    $this->currentBlock = $block;
    ob_start();
  }

  /**
   * End a capturing block
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
 * When a template cannout be found
 * @package Jivoo\View
 */
class TemplateNotFoundException extends Exception { }