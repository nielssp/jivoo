<?php

class JsonHelper extends Helper {
  
  protected $modules = array('Templates');
  
  public function respond($response) {
    $template = new Template($this->m
      ->Templates, $this->m
      ->Routing);
    $template->json = json_encode($response);
    $template->render('default.json');
  }
}
