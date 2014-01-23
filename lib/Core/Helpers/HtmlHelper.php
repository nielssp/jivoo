<?php
/**
 * HTML helper. Adds some useful methods when working with HTML views.
 * @package Core\Helpers
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
  private function addAttributes($options) {
    $html = '';
    if (isset($options['data'])) {
      $data = $options['data'];
      unset($options['data']);
      foreach ($data as $key => $value) {
        $html .= ' data-' . $key . '="' . h($value) . '"';
      }
    }
    foreach ($options as $attribute => $value) {
      $html .= ' ' . $attribute . '="' . h($value) . '"';
    }
    return $html;
  }

  /**
   * Create a link
   * @param string $label Label for link
   * @param array|ILinkable|string|null $route Route for link, default is frontpage, see
   * {@see Routing}.
   * @param array $attributes Associative array of attributes to add to link.
   * @return string|false An HTML link or false if invalid route.
   */
  public function link($label, $route = null, $attributes = array()) {
    try {
      $url = $this->m->Routing->getLink($route);
      if (!isset($attributes['class']) AND $this->m->Routing->isCurrent($route)) {
        $attributes['class'] = 'current';
      }
    }
    catch (InvalidRouteException $e) {
      Logger::logException($e);
      return false;
    }
    return '<a href="' . h($url) . '"' . $this->addAttributes($attributes)
        . '>' . $label . '</a>';
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
