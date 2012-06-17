<?php
// Module
// Name           : Links
// Version        : 0.2.0
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

    require_once(p(MODELS . 'Link.php'));

    if (!$this->m->Database->tableExists('links')) {
      $this->m->Database->createQuery('links')
        ->addInt('id', TRUE, TRUE)
        ->setPrimaryKey('id')
        ->addVarchar('menu', 255)
        ->addVarchar('type', 10)
        ->addVarchar('title', 255)
        ->addText('path')
        ->addIndex(FALSE, 'menu')
        ->execute();
      $newInstall = TRUE;
    }

    ActiveRecord::addModel('Link', 'links');

    if ($newInstall) {
      $link = Link::create();
      $link->menu = 'main';
      $link->type = 'home';
      $link->title = tr('Home');
      $link->path = '';
      $link->save();

      $link = Link::create();
      $link->menu = 'main';
      $link->type = 'path';
      $link->title = tr('About');
      $link->path = 'about';
      $link->save();
    }
  }

  public function getPath(Link $record) {

  }

  public function getLink(Link $record) {
    return $this->m->Http->getLink($record->getPath());
  }

}
