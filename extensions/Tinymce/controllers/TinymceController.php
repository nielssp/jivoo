<?php

class TinymceController extends ApplicationController {
  public function init() {
    $this->scriptUrl = $this->e->Tinymce->getScriptUrl();
    $this->render('tinymce/init.js');
  }
}
