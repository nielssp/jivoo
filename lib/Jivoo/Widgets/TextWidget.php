<?php
/**
 * Simple text widget
 */
class TextWidget extends Widget {
  public function main($config) {
    $this->text = $config['text'];
    return $this->fetch();
  }
}