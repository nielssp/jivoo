<?php
class ViewExtensions {
  
  private $view;
  
  private $extensions = array();
  
  public function __construct(View $view) {
    $this->view = $view;
  }
  
  public function add($template, IViewExtension $extension, $hook = null) {
    if (!isset($this->extensions[$template]))
      $this->extensions[$template] = array();
    $this->extensions[$template] = array(
      'extension' => $extension,
      'hook' => $hook
    );
  }
  
  public function extensions($hook = null, $type = 'IViewExtension') {
    $template = $this->view->template->getCurrent();
    $extensions = array();
    if (isset($this->extensions[$template])) {
      foreach ($this->extensions[$template] as $extInfo) {
        if ($hook == null or $extInfo['hook'] == $hook) {
          $extension = $extInfo['extension'];
          if ($extension instanceof $type) {
            if ($extension->init())
              $extensions[] = $extension;
          }
        }
      }
    }
    return $extensions;
  }
}