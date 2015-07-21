<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Helpers\Helper;
use Jivoo\Routing\Response;

/**
 * Jivoo toolkit helper.
 */
class JtkHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Jtk', 'Themes');

  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Snippet');
  
  /**
   * @var int[] Preferred icon sizes.
   */
  private $iconSizes = array(
    'xs' => 10,
    'sm' => 10,
    'md' => 12,
    'lg' => 14
  );

  /**
   * {@inheritdoc}
   */
  protected function init() {
    if (isset($this->view->compiler))
      $this->view->compiler->addMacros(new JtkMacros());
  }
  
  /**
   * Get a JTK tool.
   * @param string $toolName Tool name.
   * @return PartialJtkSnippet A partial JTK snippet that can be used to
   * configure the tool before outputting.
   * @throws \Exception If tool not found.
   */
  public function __get($toolName) {
    $tool = $this->m->Jtk->getTool($toolName);
    if (!isset($tool))
      throw new \Exception(tr('Tool not found: %1', $toolName));
    return new PartialJtkSnippet($tool, $tool->getObject());
  }
  
  /**
   * Get and invoke a JTK tool.
   * @param string $tool Tool name.
   * @param mixed $param Parameters for tool.
   * @return string HTML source for tool.
   * @throws \Exception If tool not found.
   */
  public function __call($tool, $parameters) {
    $tool = $this->m->Jtk->getTool($tool);
    if (isset($tool)) {
      $response = $tool->__invoke($parameters);
      if ($response instanceof Response)
        return $response->body;
      return $response;
    }
    throw new \InvalidMethodException(tr('Invalid method: %1', $tool));
  }
  
  public function button($label = null, $icon = null, $context = null, $size = null) {
    
  }
  
  /**
   * Set the JTK theme.
   * @param string $theme Theme name.
   * @todo This doesn't make much sense.
   */
  public function setTheme($theme) {
    $this->m->Themes->load($theme);
  }
  
  /**
   * Apply or extend a JTK layout.
   * @param string $layout Layout name.
   */
  public function layout($layout = 'default') {
    if (isset($this->view->template)) {
      if ($this->view->template->isLayout())
        $this->view->template->extend('jivoo/jtk/layout/' . $layout . '.html');
      else
        $this->view->template->layout('jivoo/jtk/layout/' . $layout . '.html');
    }
  }
}
