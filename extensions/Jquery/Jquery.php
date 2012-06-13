<?php
// Extension
// Name         : jQuery JavaScript framework
// Category     : JavaScript jQuery
// Website      : http://jquery.com
// Version      : 1.7.1
// Dependencies : templates

class Jquery extends ExtensionBase {
  protected function init() {
    $this->m->Templates->addScript(
      'jquery',
      $this->getLink('js/jquery-1.7.1.min.js')
    );
  } 
}
