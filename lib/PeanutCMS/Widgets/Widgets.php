<?php
// Module
// Description    : The PeanutCMS widget system
// Author         : apakoh.dk
// Dependencies   : Core/Database Core/Routing Core/Templates Core/Controllers
//                  Core/Authentication PeanutCMS/Backend Core/Editors
//                  Core/Models

/**
 * Widget system module
 * @package PeanutCMS\Widgets
 */
class Widgets extends ModuleBase {
  protected function init() {
    $this->m->Templates->insertHtml('sidebar', 'sidebar', 'div', array(), 'widgets');
  }
  
  /**
   * Register a widget
   * @param WidgetBase $widget Widget
   */
  public function register(WidgetBase $widget) {
    
  }
}