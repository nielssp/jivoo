<?php

class TinymceController extends ApplicationController {
  public function init() {
    $this->scriptUrl = $this->e->Tinymce->getScriptUrl();
    $this->styleUrl = $this->e->Tinymce->getStyleUrl();
    $this->render('tinymce/init.js');
  }
}
