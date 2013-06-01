<?php

class JsonHelper extends Helper {
  public function respond($response = null) {
    $template = new Template($this->m
      ->Templates, $this->m
      ->Routing);
    if (isset($response)) {
      $template->json = json_encode($response);
    }
    else {
      $data = $this->controller
        ->getData();
      $template->json = json_encode($data);
    }
    $template->render('default.json');
  }
}
