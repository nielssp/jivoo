<?php
/**
 * Simple text widget
 * @package PeanutCMS\Widgets
 */
class TextWidget extends Widget {
  public function main($config) {
    $this->text = $config['text'];
    return $this->fetch();
  }
}