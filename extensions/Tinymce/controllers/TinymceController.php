<?php

class TinymceController extends ApplicationController {
  public function init() {
    $this->scriptUrl = $this->e->Tinymce->getScriptUrl();
    $this->styleUrl = $this->e->Tinymce->getStyleUrl();
//     header("Cache-Control: max-age=3600");
//     header("Expires: " . date('r', time() + 60 * 60));
    $this->render('tinymce/init.js');
  }
}
