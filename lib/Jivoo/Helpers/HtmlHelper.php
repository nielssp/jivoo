<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Core\Utilities;

/**
 * HTML helper. Adds some useful methods when working with HTML views.
 */
class HtmlHelper extends Helper {
  /**
   * @var array Associative array of begin and end tags.
   */
  private $endTags = array('<ul>' => '</ul>', '<li>' => '</li>');
  
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
   * @param array $options Associative array of attributes.
   * @return string Attributes
   */
  public function addAttributes($options) {
    $html = '';
    if (isset($options['data'])) {
      $data = $options['data'];
      unset($options['data']);
      foreach ($data as $key => $value) {
        if ($value != null)
          $html .= ' data-' . $key . '="' . h($value) . '"';
      }
    }
    foreach ($options as $attribute => $value) {
      if ($value != null)
        $html .= ' ' . $attribute . '="' . h($value) . '"';
    }
    return $html;
  }
  
  /**
   * Create an HTML tag.
   * @param string $tag Tag.
   * @return HtmlNode Html node.
   */
  public function create($tag) {
  }

  /**
   * Insert an image.
   * @param $file Path to file (can be an asset or an absolute path).
   * @param array $attributes Associative array of attributes to add to image.
   * @return string HTML image.
   */
  public function img($file, $attributes = array()) {
    if (!isset($attributes['alt']))
      $attributes['alt'] = $file;
    if (!Utilities::isAbsolutePath($file))
      $file = $this->view->file($file);
    $attributes['src'] = $file;
    return '<img' . $this->addAttributes($attributes) . ' />';
  }

  /**
   * Create a link
   * @param string $label Label for link
   * @param array|ILinkable|string|null $route Route for link, default is
   *        frontpage, see {@see Routing}.
   * @param array $attributes Associative array of attributes to add to link.
   * @return string HTML link.
   */
  public function link($label, $route = null, $attributes = array()) {
    try {
      $url = $this->m->Routing->getLink($route);
      if ($url != '')
        $attributes['href'] = $url;
      if (!isset($attributes['class']) and $this->m->Routing->isCurrent($route))
        $attributes['class'] = 'current';
      return '<a' . $this->addAttributes($attributes) .
             '>' . $label . '</a>';
    }
    catch (InvalidRouteException $e) {
      Logger::logException($e);
      return '<a href="#invalid-route" class="invalid">' . $label . '</a>';
    }
  }

  /**
   * Create a nested list from a nested array structure
   * @param array $list Nested array structure
   * @param string $listTag List begin tag, default is <code><ul></code>
   * @param string $itemTag Item begin tag, default is <code><li></code>
   * @return string An HTML nested list.
   */
  public function nestedList($list, $listTag = '<ul>', $itemTag = '<li>') {
    if (is_string($list)) {
      return $list;
    }
    else if (is_array($list)) {
      $listEndTag = $this->getEndTag($listTag);
      $itemEndTag = $this->getEndTag($itemTag);
      $output = $listTag . PHP_EOL;
      $li = false;
      foreach ($list as $item) {
        if ($li AND is_string($item)) {
          $output .= $itemEndTag . PHP_EOL;
        }
        if (is_string($item) OR !$li) {
          $output .= $itemTag;
          $li = true;
        }
        $output .= $this->nestedList($item, $listTag, $itemTag);
      }
      if ($li) {
        $output .= $itemEndTag . PHP_EOL;
      }
      $output .= $listEndTag . PHP_EOL;
      return $output;
    }
    return '';
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
