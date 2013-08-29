<?php
class TextWidget extends WidgetBase {
  public function main($config) {
    $this->text = $config['text'];
    return $this->view->fetch();
  }
}