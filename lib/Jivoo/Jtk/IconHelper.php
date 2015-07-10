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
   * Add an icon provider.
   * @param IIconProvider $provider Icon provider.
   */
  public function addProvider(IIconProvider $provider) {
    $this->providers[] = $provider;
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
   * Create a button with a label and an icon.
   * @param string $label Button label.
   * @param string $icon Button icon.
   * @param string[] $attributes Additional attributes for button.
   * @return string HTML source for button.
   */
  public function button($label, $icon = null, $attributes = array()) {
    if (isset($icon))
      $icon = '<span class="icon">' . $this->icon($icon) . '</span>';
    else
      $icon = '';
    return '<button' .
           $this->Html->addAttributes($attributes) . '>' . $icon .
           '<span class="label">' . $label . '</span></button>';
  }

  /**
   * Create a link with only an icon.
   * @param string $label Link label.
   * @property string|array|Jivoo\Routing\ILinkable|null $route A route, see
   * {@see Jivoo\Routing\Routing}.
   * @param string $icon Icon identifier.
   * @param string[] $attributes Additional attributes for link.
   * @return string HTML source for link.
   */
  public function iconLink($label, $route, $icon = null, $attributes = array()) {
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
      Logger::logException($e);
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
   */
  public function link($label, $route, $icon = null, $count = null, $attributes = array()) {
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
      if (isset($count))
        $count = '<span class="count">' . $count . '</span>';
      else
        $count = '';
      return '<a href="' . h($url) . '"' .
             $this->Html->addAttributes($attributes) . '>' . $icon .
             '<span class="label">' . $label . '</span>' . $count . '</a>';
    }
    catch (InvalidRouteException $e) {
      Logger::logException($e);
      return '<a href="#invalid-route" class="invalid"><span class="label">' .
             $label . '</span></a>';
    }
  }
}
