<?php

class FormHelper extends ApplicationHelper {
  public function begin() {
    return '<form action="' . $this->getLink(array()) . '" method="post">';
  }
  
  public function end() {
    return '</form>';
  }
}