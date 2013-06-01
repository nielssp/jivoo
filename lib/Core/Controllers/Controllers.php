<?php
// Module
// Name           : Controllers
// Version        : 0.3.14
// Description    : For contollers
// Author         : apakoh.dk
// Dependencies   : Core/Routing Core/Templates
//                  Core/Helpers

class Controllers extends ModuleBase {
  protected function init() {
    $dir = opendir($this->p('controllers', ''));
    while ($file = readdir($dir)) {
      $split = explode('.', $file);
      if (isset($split[1]) AND $split[1] == 'php') {
      }
    }

    $controller = new ApplicationController($this->m->Routing, $this->config);
    $controller->setRoute('notFound', 1);
  }
}