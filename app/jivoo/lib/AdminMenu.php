<?php
class AdminMenu extends AppListener {
  
  protected $handlers = array('afterLoadModules');

  public function afterLoadModules() {
    $menu = new IconMenu(tr('Main'));
    $menu->fromArray(array(
      'status' => IconMenu::menu(tr('Status'), null, null, array(
        IconMenu::item(tr('Dashboard'), 'Admin::dashboard', 'meter'),
        IconMenu::item(tr('Install updates'), 'Admin::update', 'download3', '3'),
      )),
      'content' => IconMenu::menu(tr('Content'), null, null, array(
        'posts' => IconMenu::menu(tr('Posts'), 'Admin::Posts', 'newspaper', array(
          IconMenu::item(tr('Posts'), 'Admin::Posts::index'),
          IconMenu::item(tr('Tags'), null),
        )),
        'pages' => IconMenu::item(tr('Pages'), 'Admin::Pages', 'file'),
        'comments' => IconMenu::item(tr('Comments'), 'Admin::Comments', 'bubbles'),
        'media' => IconMenu::item(tr('Media'), 'Admin::Media', 'images'),
      )),
      'appearance' => IconMenu::menu(tr('Appearance'), null, null, array(
        'themes' => IconMenu::item(tr('Themes'), 'Admin::Themes', 'paint-format'),
        'widgets' => IconMenu::item(tr('Widgets'), 'Admin::Widgets', 'dashboard'),
        'menus' => IconMenu::item(tr('Menus'), 'Admin::Menus', 'menu'),
      )),
      'settings' => IconMenu::menu(tr('Settings'), array(), null, array(
        'users' => IconMenu::menu(tr('Users'), 'Admin::Users', 'users', array(
          IconMenu::item(tr('Users'), 'Admin::Users::index'),
          IconMenu::item(tr('Groups'), null),
        )),
        'extensions' => IconMenu::item(tr('Extensions'), 'Admin::Extensions', 'powercord'),
        'configuration' => IconMenu::item(tr('Configuration'), 'Admin::Configuration', 'wrench'),
      )),
      'about' => IconMenu::menu(tr('About'), array(), null, array(
        IconMenu::item(tr('Help & support'), null, 'support'),
        IconMenu::item(tr('About Jivoo'), 'Admin::about', 'jivoo'),
      )),
    ));
    $this->m->Administration->menu['main'] = $menu;
    $this->m->Administration->menu['shortcuts'] = IconMenu::menu(
      tr('Shortcuts'), null, null, array(
        IconMenu::item($this->config['Templates']['title'], null, 'home'),
        IconMenu::item(tr('Dashboard'), 'Admin::dashboard', 'meter'),
        IconMenu::menu(tr('Add'), array('fragment' => ''), 'plus', array(
          IconMenu::item(tr('Add post'), 'Admin::Posts::add'),
          IconMenu::item(tr('Add page'), 'Admin::Pages::add'),
          IconMenu::item(tr('Add comment'), 'Admin::Comments::add'),
        )),
      )
    );
  }
}
