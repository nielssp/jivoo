<?php
class AdminMenu extends AppListener {
  
  protected $handlers = array('afterLoadModules');

  public function afterLoadModules() {
    $menu = new IconMenu(tr('Main'));
    $menu->fromArray(array(
      'status' => IconMenu::menu(tr('Status'), null, null, array(
        IconMenu::item(tr('Dashboard'), 'Admin::dashboard', 'meter'),
        IconMenu::item(tr('Install updates'), null, 'download3', '3'),
      )),
      'content' => IconMenu::menu(tr('Content'), null, null, array(
        'posts' => IconMenu::menu(tr('Posts'), 'Admin::Posts', 'newspaper', array(
          IconMenu::item(tr('All posts'), 'Admin::Posts::index'),
          IconMenu::item(tr('Add post'), 'Admin::Posts::add'),
          IconMenu::item(tr('Tags'), null),
        )),
      )),
      'appearance' => IconMenu::menu(tr('Appearance'), null, null, array(
      )),
      'settings' => IconMenu::menu(tr('Settings'), array(), null, array(
      )),
      'about' => IconMenu::menu(tr('About'), array(), null, array(
        IconMenu::item(tr('Help & support'), null, 'support'),
        IconMenu::item(tr('About Jivoo'), 'Admin::about', 'jivoo'),
      )),
    ));
    $this->m->Administration->menu['main'] = $menu;
  }
}