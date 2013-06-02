<?php

class TinymceController extends Controller {
  public function initJs() {
    $this->scriptUrl = $this->Tinymce->getScriptUrl();
    $this->styleUrl = $this->Tinymce->getStyleUrl();
    //     header("Cache-Control: max-age=3600");
    //     header("Expires: " . date('r', time() + 60 * 60));
    $this->render('tinymce/init.js');
  }
}
