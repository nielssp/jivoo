<?php

class Template extends ApplicationTemplate {

  public function render($_templateName) {
    extract($this->data, EXTR_SKIP);
    extract($this->getTemplateData($_templateName), EXTR_SKIP);
    $_templateFile = $this->getTemplate($_templateName);
    if ($_templateFile !== FALSE) {
      require($_templateFile);
    }
    else {
      echo tr('The template "%1" could not be found', $_templateName);
    }
  }

}
