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

  public function link($label, $controller, $action = 'index', $paramters = array()) {
    $url = $this->m->Routes->getLink($controller, $action, $paramters);
    return '<a href="' . $url . '">' . $label . '</a>';
  }

  public function nestedList($list, $listTag = '<ul>', $itemTag = '<li>') {
    if (is_string($list)) {
      return $list;
    }
    else if (is_array($list)) {
      $listEndTag = $this->getEndTag($listTag);
      $itemEndTag = $this->getEndTag($itemTag);
      $output = $listTag . PHP_EOL;
      $li = FALSE;
      foreach ($list as $item) {
        if ($li AND is_string($item)) {
          $output .= $itemEndTag . PHP_EOL;
        }
        if (is_string($item) OR !$li) {
          $output .= $itemTag;
          $li = TRUE;
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

}
