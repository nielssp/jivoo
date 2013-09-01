<?php
// Module
// Name           : Links
// Description    : The PeanutCMS graphical menu system
// Author         : apakoh.dk
// Dependencies   : Core/Database Core/Routing Core/Models
//                  Core/Templates Core/Controllers
//                  Core/Authentication PeanutCMS/Backend

/**
 * Menu system
 * @package PeanutCMS\Links
 */
class Links extends ModuleBase {

  protected function init() {

    if ($this->m->Database->isNew('links')) {
      $link = $this->m->Models->Link->create();
      $link->menu = 'main';
      $link->position = 0;
      $link->title = tr('Home');
      $link->setRoute();
      $link->save();

      $link = $this->m->Models->Link->create();
      $link->menu = 'main';
      $link->position = 1;
      $link->title = tr('About');
      $link->setRoute(array('path' => array('about')));
      $link->save();

      $link = $this->m->Models->Link->create();
      $link->menu = 'main';
      $link->position = 2;
      $link->title = tr('Get help');
      $link->setRoute('http://apakoh.dk');
      $link->save();

      $link = $this->m->Models->Link->create();
      $link->menu = 'main';
      $link->position = 3;
      $link->title = tr('Admin');
      $link->setRoute($this->m->Backend);
      $link->save();
    }

    $this->m->Routing->autoRoute('LinksBackend');
    
    $this->m->Backend['content']->setup(tr('Content'), 2)
      ->item(tr('Menu'), 'Backend::Links::menu', 12, 'backend.links.manage');
  }

}
