<?php
/**
 * View for presenting output to user
 * @package Jivoo\Templates
 */
class View extends ViewBase {
  protected function embed($_template, $_data = array()) {
    extract($_data, EXTR_SKIP);
    extract($this->data, EXTR_SKIP);
    if (isset($this->templateData[$_template])) {
      extract($this->templateData[$_template], EXTR_SKIP);
    }
    $_file = $this->findTemplate($_template);
    if ($_file === false) {
      throw new TemplateNotFoundException(tr('Template not found: %1', $_template));
    }
    require $_file;
  }
}