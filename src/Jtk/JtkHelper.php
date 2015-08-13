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
  protected $helpers = array('Snippet', 'Icon', 'Html');
  
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
      return parent::__get($toolName);
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
  
  /**
   * Create a JTK link with an optional icon and badge.
   * @param string $label Link label.
   * @param array|\Jivoo\Routing\ILinkable|string|null $route Route for link,
   * default is frontpage, see {@see \Jivoo\Routing\Routing}.
   * @param string[]|string $attributes Attributes, see
   * {@see \Jivoo\Helpers\Html::readAttributes}.
   * @return string Link html.
   */
  public function link($label = null, $route = null, $attributes = array()) {
    $link = $this->Html->create('a', $attributes);
    if (isset($label))
      $link->html('<span class="label">' . $label . '</span>');
    
    if ($link->hasProp('icon'))
      $link->prepend('<span class="icon">' . $this->Icon->icon($link['icon']) . '</span>');
    
    if ($link->hasProp('badge'))
      $link->append('<span class="badge">' . $link['badge'] . '</span>');

    return $this->Html->link($link->html(), $route, $link->attr());
  }

  /**
   * Create a JTK link with an icon. Unlike {@see link}
   * the label is not shown and is used as a title tooltip instead.
   * @param string $label Link label.
   * @param array|\Jivoo\Routing\ILinkable|string|null $route Route for link,
   * default is frontpage, see {@see \Jivoo\Routing\Routing}.
   * @param string[]|string $attributes Attributes, see
   * {@see \Jivoo\Helpers\Html::readAttributes}.
   * @return string Button or link html.
   */
  public function iconLink($label = null, $route = null, $attributes = array()) {
    $link = $this->Html->create('a', $attributes);
    $link['title'] = $label;
    return $this->link(null, $route, $link->attr());
  }
  
  /**
   * Create a JTK button with optional icon, context, size and badge. If the
   * 'route'-attribute is set, the button will be a link.
   * @param string $label Button label.
   * @param string[]|string $attributes Attributes, see
   * {@see \Jivoo\Helpers\Html::readAttributes}.
   * @return string Button or link html.
   */
  public function button($label = null, $attributes = array()) {
    $button = $this->Html->create('button', $attributes);
    if (isset($label))
      $button->html('<span class="label">' . $label . '</span>');
    
    if ($button->hasProp('icon'))
      $button->prepend('<span class="icon">' . $this->Icon->icon($button['icon']) . '</span>');
    
    if ($button->hasProp('badge'))
      $button->append('<span class="badge">' . $button['badge'] . '</span>');
    
    if ($button->hasProp('size'))
      $button->addClass('button-' . $button['size']);
    
    if ($button->hasProp('context'))
      $button->addClass('button-' . $button['context']);
    
    if ($button->hasProp('ctx'))
      $button->addClass('button-' . $button['ctx']);
    
    if ($button->hasProp('route')) {
      $button->addClass('button');
      return $this->Html->link($button->html(), $button['route'], $button->attr());
    }
    else {
      if (!isset($button['type']))
        $button['type'] = 'button';
      return $button->toString();
    }
  }

  /**
   * Create a JTK button with optional link, context, size and badge. If the
   * 'route'-attribute is set, the button will be a link. Unlike {@see button}
   * the label is not shown and is used as a title tooltip instead.
   * @param string $label Button label.
   * @param string[]|string $attributes Attributes, see
   * {@see \Jivoo\Helpers\Html::readAttributes}.
   * @return string Button or link html.
   */
  public function iconButton($label = null, $attributes = array()) {
    $button = $this->Html->create('button', $attributes);
    $button['title'] = $label;
    return $this->button(null, $button->attr());
  }

  /**
   * Create a JTK badge with optional icon, context, and size. If the
   * 'route'-attribute is set, the badge will be a link.
   * @param string $label Badge label.
   * @param string[]|string $attributes Attributes, see
   * {@see \Jivoo\Helpers\Html::readAttributes}.
   * @return string Badge or link html.
   */
  public function badge($label, $attributes = array()) {
    $badge = $this->Html->create('span', $attributes);
    if (isset($label))
      $badge->html('<span class="label">' . $label . '</span>');
    $badge->addClass('badge');
    if ($badge->hasProp('context'))
      $badge->addClass('badge-' . $badge['context']);
    if ($badge->hasProp('ctx'))
      $badge->addClass('badge-' . $badge['ctx']);
    if ($badge->hasProp('icon'))
      $badge->prepend('<span class="icon">' . $this->Icon->icon($badge['icon']) . '</span>');

    if ($badge->hasProp('route'))
      return $this->Html->link($badge->html(), $badge['route'], $badge->attr());
    return $badge->toString();
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
