<?php
// Module
// Name           : Links
// Version        : 0.3.0
// Description    : The PeanutCMS graphical menu system
// Author         : PeanutCMS
// Dependencies   : Errors Configuration Database Routes Templates Http
//                  Authentication Backend

/*
 * Menu system
 *
 * @package PeanutCMS
 */

/**
 * Links class
 */
class Links extends ModuleBase {

  private $controller;

  protected function init() {
    $newInstall = false;

    $linksSchema = new linksSchema();

    $newInstall = $this->m->Database->migrate($linksSchema) == 'new';

    $this->m->Database->links->setSchema($linksSchema);

    Link::connect($this->m->Database->links);

    if ($newInstall) {
      $link = Link::create();
      $link->menu = 'main';
      $link->position = 0;
      $link->title = tr('Home');
      $link->setRoute();
      $link->save();

      $link = Link::create();
      $link->menu = 'main';
      $link->position = 1;
      $link->title = tr('About');
      $link->setRoute(array('path' => array('about')));
      $link->save();

      $link = Link::create();
      $link->menu = 'main';
      $link->position = 2;
      $link->title = tr('Get help');
      $link->setRoute('http://apakoh.dk');
      $link->save();

      $link = Link::create();
      $link->menu = 'main';
      $link->position = 3;
      $link->title = tr('Admin');
      $link->setRoute($this->m->Backend);
      $link->save();
    }

    $this->controller = new LinksController($this->m->Routes, $this->m->Configuration->getSubset('links'));
    
    $this->m->Backend['content']->setup(tr('Content'), 2);
    $this->m->Backend['content']['links-manage']->setup(tr('Menu'), 12)
      ->permission('backend.links.manage')->autoRoute($this->controller, 'menu');  
  }

}
