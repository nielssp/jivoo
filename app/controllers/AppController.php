<?php

class AppController extends Controller {
  public function notFound() {
    $this->render('404.html');
  }
}