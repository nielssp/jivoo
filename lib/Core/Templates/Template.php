<?php
/**
 * Concrete template class used for outputting templates to browser
 * @package Core\Templates
 */
class Template extends TemplateBase {

  public function render($_templateName, $_return = false) {
    extract($this->data, EXTR_SKIP);
    extract($this->getTemplateData($_templateName), EXTR_SKIP);
    $_templateFile = $this->getTemplate($_templateName, $_return);
    if ($_return) {
      ob_start();
    }
    if ($_templateFile !== false) {
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
