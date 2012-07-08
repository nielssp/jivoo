<?php
// Module
// Name           : Links
// Version        : 0.3.0
// Description    : The PeanutCMS graphical menu system
// Author         : PeanutCMS
// Dependencies   : errors configuration database routes templates http
//                  users backend

/*
 * Menu system
 *
 * @package PeanutCMS
 */

/**
 * Links class
 */
class Links extends ModuleBase {

  protected function init() {
    $newInstall = FALSE;

    $linksSchema = new linksSchema();

    $newInstall = $this->m->Database->migrate($linksSchema) == 'new';

    $this->m->Database->links->setSchema($linksSchema);

    Link::connect($this->m->Database->links);

    if ($newInstall) {
      $link = Link::create();
      $link->menu = 'main';
      $link->title = tr('Home');
      $link->setRoute();
      $link->save();

      $link = Link::create();
      $link->menu = 'main';
      $link->title = tr('About');
      $link->setRoute(array('path' => array('about')));
      $link->save();

      $link = Link::create();
      $link->menu = 'main';
      $link->title = tr('Get help');
      $link->setRoute('http://apakoh.dk');
      $link->save();

      $link = Link::create();
      $link->menu = 'main';
      $link->title = tr('Admin');
      $link->setRoute($this->m->Backend);
      $link->save();
    }
  }

  public function getPath(Link $record) {

  }

  public function getLink(Link $record) {
    return $this->m->Http->getLink($record->getPath());
  }

}
