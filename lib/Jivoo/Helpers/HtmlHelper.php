<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Core\Utilities;
use Jivoo\Routing\InvalidRouteException;
use Jivoo\Core\Logger;

/**
 * HTML helper. Adds some useful methods when working with HTML views.
 */
class HtmlHelper extends Helper {
  /**
   * @var string Class to put on current links
   */
  private $classIfCurrent = 'current';

  /**
   * Get end tag for a begin tag
   * @param string $tag Begin tag, e.g. '<ul>'
   * @return string End tag, e.g. '</ul>'
   */
  public function getEndTag($tag) {
    if (!isset($this->endTags[$tag])) {
      $matches = array();
      preg_match('/<\s*([a-zA-Z0-9]+)/', $tag, $matches);
      $this->endTags[$tag] = '</' . $matches[1] . '>';
    }
    return $this->endTags[$tag];
  }

  /**
   * Convert an array of attributes into valid HTML.
   * @param string[] $attributes Attributes, see {@see Html::readAttributes}.
   * @return string Attributes
   */
  public function addAttributes($attributes) {
    $output = '';
    $attributes = Html::readAttributes($attributes);
    foreach ($attributes as $name => $value) {
      if (is_string($value) or $value === true) {
        $output .= ' ' . $name;
        if ($value !== true)
          $output .= '="' . h($value) . '"';
      }
    }
    return $output;
  }
  
  /**
   * Create an HTML tag.
   * @param string $tag Tag.
   * @param string[] $attributes Attributes, see {@see Html::readAttributes}.
   * @return Html Html node.
   */
  public function create($tag, $attributes = array()) {
    $html = new Html($tag);
    $html->attr($attributes);
    return $html;
  }

  /**
   * Insert an image.
   * @param $file Path to file (can be an asset or an absolute path).
   * @param string[] $attributes Attributes, see {@see Html::readAttributes}.
   * @return string HTML image.
   */
  public function img($file, $attributes = array()) {
    $img = $this->create('img');
    $img->attr('alt', $file);
    $img->attr($attributes);
    if (!Utilities::isAbsolutePath($file))
      $file = $this->view->file($file);
    $img->attr('src', $file);
    return $img->toString();
  }

  /**
   * Create a link
   * @param string $label Label for link
   * @param array|\Jivoo\Routing\ILinkable|string|null $route Route for link,
   * default is frontpage, see {@see \Jivoo\Routing\Routing}.
   * @param string[] $attributes Attributes, see {@see Html::readAttributes}.
   * @return string HTML link.
   */
  public function link($label, $route = null, $attributes = array()) {
    $a = $this->create('a', $attributes);
    $a->html($label);
    try {
      $url = $this->m->Routing->getLink($route);
      if ($url != '')
        $a->attr('href', $url);
      if ($this->m->Routing->isCurrent($route))
        $a->addClass('current');
      return $a->toString();
    }
    catch (InvalidRouteException $e) {
      Logger::logException($e);
      $a->attr('href', '#invalid-route');
      $a->addClass('invalid');
      return $a->toString();
    }
  }

  /**
   * Clean a URL
   * @param string $url URL
   * @return string URL
   */
  public function cleanUrl($url) {
    if (preg_match('/^https?:\/\//i', $url) == 0) {
      $url = '';
    }
    return h($url);
  }
}