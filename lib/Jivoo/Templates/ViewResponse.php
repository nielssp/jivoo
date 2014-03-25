<?php

class ViewResponse extends Response {
  private $view;
  private $template;

  public function __construct($status, View $view, $template) {
    parent::__construct($status, Utilities::getContentType($template));
    $this->view = $view;
    $this->template = $template;
  }

  public function getBody() {
    return $this->view->fetch($this->template);
  }
}
