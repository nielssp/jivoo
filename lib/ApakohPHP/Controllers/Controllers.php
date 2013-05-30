<?php
// Module
// Name           : Controllers
// Version        : 0.3.14
// Description    : For contollers
// Author         : apakoh.dk
// Dependencies   : ApakohPHP/Routing ApakohPHP/Templates
//                  ApakohPHP/Helpers

class Controllers extends ModuleBase {
  protected function init() {
    $dir = opendir($this->p('controllers', ''));
    while ($file = readdir($dir)) {
      $split = explode('.', $file);
      if (isset($split[1]) AND $split[1] == 'php') {
      }
    }
  }
}