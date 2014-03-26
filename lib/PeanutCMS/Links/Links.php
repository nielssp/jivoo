<?php
// Module
// Name           : Links
// Description    : The PeanutCMS graphical menu system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Database Jivoo/Routing Jivoo/Models
//                  Jivoo/Templates Jivoo/Controllers
//                  Jivoo/Authentication PeanutCMS/Backend

/**
 * Menu system
 * @package PeanutCMS\Links
 */
class Links extends ModuleBase {

  protected function init() {

    $this->m->Routing->autoRoute('LinksBackend');
    
    $this->m->Backend['appearance']->setup(tr('Appearance'), 4)
      ->item(tr('Menus'), 'Backend::Links::menus', 10, 'backend.links.index');
  }

}
