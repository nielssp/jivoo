<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View;

/**
 * Collection of view extensions.
 */
class ViewExtensions {
  /**
   * @var View View.
   */
  private $view;
  
  /**
   * @var array[] Array of view extensions and hooks.
   */
  private $extensions = array();
  
  /**
   * Construct view extension collection.
   * @param View $view The view.
   */
  public function __construct(View $view) {
    $this->view = $view;
  }
  
  /**
   * Add a view extension.
   * @param string $template Template to extend.
   * @param ViewExtension $extension The view extension.
   * @param string $hook Names of hooks to attach view extension to.
   */
  public function add($template, ViewExtension $extension, $hook = null) {
    if (!isset($this->extensions[$template]))
      $this->extensions[$template] = array();
    $this->extensions[$template][] = array(
      'extension' => $extension,
      'hook' => $hook
    );
  }
  
  /**
   * Access view extensions attached to a hook, and make sure they
   * implement the desired interface.
   * @param string $hook Hook name.
   * @return ViewExtension[] List of view extensions for hook.
   */
  public function extensions($hook = null) {
    $template = $this->view->template->getCurrent();
    $extensions = array();
    if (isset($this->extensions[$template])) {
      foreach ($this->extensions[$template] as $extInfo) {
        if ($hook == null or $extInfo['hook'] == $hook) {
          $extension = $extInfo['extension'];
          if ($extension->prepare())
            $extensions[] = $extension;
        }
      }
    }
    return $extensions;
  }
}