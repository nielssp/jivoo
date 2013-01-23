<?php
// Extension
// Name         : jQuery JavaScript framework
// Category     : JavaScript jQuery
// Website      : http://jquery.com
// Version      : 1.7.1
// Dependencies : Templates Assets

class Jquery extends ExtensionBase {
  protected function init() {
    $this->m
      ->Templates
      ->addScript('jquery', $this->getAsset('js/jquery-1.7.1.min.js'));
  }
}
