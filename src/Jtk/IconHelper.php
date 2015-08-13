<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Helpers\Helper;

/**
 * Helper for inserting icons using icon provideres, see {@see IIconProvider}.
 */
class IconHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Html');
  
  /**
   * @var IIconProvider[]
   */
  private $providers = array();
  
  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->addProvider(new CoreIconProvider());
  } 
   
  /**
   * Add an icon provider.
   * @param IIconProvider $provider Icon provider.
   */
  public function addProvider(IIconProvider $provider) {
    array_unshift($this->providers, $provider);
  }
  
  /**
   * Fetch and output an icon
   * @param string $icon Icon identifier.
   * @param int $size Requested icon size.
   * @return string HTML source for icon.
   */
  public function icon($icon, $size = 16) {
    foreach ($this->providers as $provider) {
      $html = $provider->getIcon($icon, $size);
      if (isset($html))
        return $html;
    }
    return '<span class="icon-unavailable"></span>';
  }

  /**
   * Fetch and output an icon
   * @param string $icon Icon identifier.
   * @param int $size Requested icon size.
   * @return string HTML source for icon.
   */
  public function __invoke($icon, $size = 16) {
    return $this->icon($icon, $size);
  }
  
  /**
   * Create a button with a label and an icon.
   * @param string $label Button label.
   * @param string $icon Button icon.
   * @param string[] $attributes Additional attributes for button.
   * @return string HTML source for button.
   * @deprecated
   */
  public function button($label, $icon = null, $attributes = array()) {
    trigger_error('IconHelper::button is deprecated', E_USER_DEPRECATED);
    if (isset($icon))
      $icon = '<span class="icon">' . $this->icon($icon) . '</span>';
    else
      $icon = '';
    if (isset($label) and $label != '')
      $label = '<span class="label">' . $label . '</span>';
    else
      $label = '';
    return '<button' . $this->Html->addAttributes($attributes) . '>'
      . $icon . $label . '</button>';
  }

  /**
   * Create a link with only an icon.
   * @param string $label Link label.
   * @property string|array|Jivoo\Routing\ILinkable|null $route A route, see
   * {@see Jivoo\Routing\Routing}.
   * @param string $icon Icon identifier.
   * @param string[] $attributes Additional attributes for link.
   * @return string HTML source for link.
   * @deprecated
   */
  public function iconLink($label, $route, $icon = null, $attributes = array()) {
    trigger_error('IconHelper::iconLink is deprecated', E_USER_DEPRECATED);
    try {
      $url = $this->m->Routing->getLink($route);
      if (!isset($attributes['class']) and $this->m->Routing->isCurrent($route))
        $attributes['class'] = 'current';
      if (!isset($attributes['title']))
        $attributes['title'] = h($label);
      if (isset($icon))
        $icon = '<span class="icon">' . $this->icon($icon) . '</span>';
      else
        $icon = '';
      return '<a href="' . h($url) . '"' .
        $this->Html->addAttributes($attributes) . '>' . $icon . '</a>';
    }
    catch (InvalidRouteException $e) {
      $this->logger->error(
        tr('Invalid route: %1', $e->getMessage()),
        array('exception' => $e)
      );
      return '<a href="#invalid-route" class="invalid"><span class="label">' .
             $label . '</span></a>';
    }
  }
  
  /**
   * Create a link with a label and an icon.
   * @param string $label Link label.
   * @property string|array|Jivoo\Routing\ILinkable|null $route A route, see
   * {@see Jivoo\Routing\Routing}.
   * @param string $icon Icon identifier.
   * @param string $count Optional number.
   * @param string[] $attributes Additional attributes for link.
   * @return string HTML source for link.
   * @deprecated
   */
  public function link($label, $route, $icon = null, $count = null, $attributes = array()) {
    trigger_error('IconHelper::link is deprecated', E_USER_DEPRECATED);
    try {
      $url = $this->m->Routing->getLink($route);
      if (!isset($attributes['class']) and $this->m->Routing->isCurrent($route))
        $attributes['class'] = 'current';
      if (isset($icon))
        $icon = '<span class="icon">' . $this->icon($icon) . '</span>';
      else
        $icon = '';
      if (isset($count))
        $count = '<span class="count">' . $count . '</span>';
      else
        $count = '';
      return '<a href="' . h($url) . '"' .
             $this->Html->addAttributes($attributes) . '>' . $icon .
             '<span class="label">' . $label . '</span>' . $count . '</a>';
    }
    catch (InvalidRouteException $e) {
      $this->logger->error(
        tr('Invalid route: %1', $e->getMessage()),
        array('exception' => $e)
      );
      return '<a href="#invalid-route" class="invalid"><span class="label">' .
             $label . '</span></a>';
    }
  }

  /**
   * Create a badge with a label and an icon.
   * @param string $label Badge label.
   * @param string $icon Icon identifier.
   * @param string $context Badge context (e.g. 'success', 'primary', 'error', etc.)
   * @return string HTML source for badge.
   * @deprecated
   */
  public function badge($label, $icon = null, $context = null) {
    trigger_error('IconHelper::badge is deprecated', E_USER_DEPRECATED);
    $class = 'badge';
    if (isset($context))
      $class .= ' badge-' . $context;
    if (isset($icon))
      $icon = '<span class="icon">' . $this->icon($icon) . '</span>';
    else
      $icon = '';
    return '<span class="' . $class . '">' . $icon .
           '<span class="label">' . $label . '</span></span>';
  }
}
