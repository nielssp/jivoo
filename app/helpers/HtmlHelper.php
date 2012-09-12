<?php

class HtmlHelper extends ApplicationHelper {

  private $endTags = array(
    '<ul>' => '</ul>',
    '<li>' => '</li>'
  );

  public function getEndTag($tag) {
    if (!isset($this->endTags[$tag])) {
      $matches = array();
      preg_match('/<\s*([a-zA-Z0-9]+)/', $tag, $matches);
      $this->endTags[$tag] = '</' . $matches[1] . '>';
    }
    return $this->endTags[$tag];
  }

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

  public function link($label, $route = null, $attributes = array()) {
    $url = $this->m->Routes->getLink($route);
    return '<a href="' . h($url) . '"' . $this->addAttributes($attributes) . '>' . $label . '</a>';
  }

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
  
  public function cleanUrl($url) {
    if (preg_match('/^https?:\/\//i', $url) == 0) {
      $url = '';
    }
    return h($url);
  }

}
