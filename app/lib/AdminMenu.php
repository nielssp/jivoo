<?php
class AdminMenu extends AppListener {
  
  protected $handlers = array('afterLoadModules');

  public function afterLoadModules() {
    $menu = new IconMenu(tr('Main'));
    $menu[] = new IconMenuItem(tr('Test'), null, 'dashboard');
    $menu['sub'] = new IconMenu(tr('Submenu'), null);
    $menu['sub'][] = new IconMenuItem(tr('Test'));
    $this->m->Administration->menu['main'] = $menu;
  }
}