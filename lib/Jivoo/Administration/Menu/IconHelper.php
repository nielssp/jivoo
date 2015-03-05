<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration\Menu;

use Jivoo\Helpers\Helper;

class IconHelper extends Helper {
  
  protected $helpers = array('Html');
  
  private $providers = array();
  
  public function addProvider(IIconProvider $provider) {
    $this->providers[] = $provider;
  }
  
  public function icon($icon, $size = 16) {
    foreach ($this->providers as $provider) {
      $html = $provider->getIcon($icon, $size);
      if (isset($html))
        return $html;
    }
    return '<span class="icon-unavailable"></span>';
  }
  
  public function button($label, $icon = null, $attributes = array()) {
    if (isset($icon))
      $icon = '<span class="icon">' . $this->icon($icon) . '</span>';
    else
      $icon = '';
    return '<button' .
           $this->Html->addAttributes($attributes) . '>' . $icon .
           '<span class="label">' . $label . '</span></button>';
  }
  
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