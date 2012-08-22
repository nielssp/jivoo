<?php

class Template extends ApplicationTemplate {

  public function render($_templateName, $_return = FALSE) {
    extract($this->data, EXTR_SKIP);
    extract($this->getTemplateData($_templateName), EXTR_SKIP);
    $_templateFile = $this->getTemplate($_templateName, $_return);
    if ($_return) {
      ob_start();
    }
    if ($_templateFile !== FALSE) {
      require($_templateFile);
    }
    else {
      echo tr('The template "%1" could not be found', $_templateName);
    }
    if ($_return) {
      return ob_get_clean();
    }
  }

}
