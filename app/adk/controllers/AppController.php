<?php
class AppController extends Controller {
  
  protected $helpers = array('Html', 'Admin', 'Icon');
  
  protected function init() {
    $menu = new IconMenu(tr('Main'));

    $apps = array();
    $this->appDir = $this->p('app') . '/..';
    $files = scandir($this->appDir);
    if ($files !== false) {
      foreach ($files as $file) {
        if ($file[0] == '.') {
          continue;
        }
        if (file_exists($this->appDir . '/' . $file . '/app.php')) {
          $apps[] = include $this->appDir . '/' . $file . '/app.php';
        }
      }
    }
    $this->apps = $apps;
    
    $appMenu = array();
    foreach ($this->apps as $app) {
      $appMenu[] = IconMenu::menu($app['name'], null, 'cog', array(
        IconMenu::item(tr('Dashboard')),
        IconMenu::item(tr('Controllers')),
        IconMenu::item(tr('Models')),
        IconMenu::item(tr('Schemas')),
      ));
    }
    
    $menu->fromArray(array(
      'status' => IconMenu::menu(tr('Status'), null, null, array(
        IconMenu::item(tr('Dashboard'), 'App::index', 'meter'),
      )),
      'applications' => IconMenu::menu(tr('Applications'), null, null, $appMenu),
      'settings' => IconMenu::menu(tr('Settings'), array(), null, array(
        IconMenu::item(tr('Settings'), null, 'wrench'),
      )),
      'about' => IconMenu::menu(tr('About'), array(), null, array(
        IconMenu::item(tr('Help & support'), null, 'support'),
        IconMenu::item(tr('About Jivoo'), null, 'jivoo'),
      )),
    ));
    $this->m->Administration->menu['main'] = $menu;
  }
  
  public function before() {
    $this->Admin->importDefaultTheme();
    // TODO move to theme or something
    $this->Icon->addProvider(new ClassIconProvider());
  }
  
  public function index() {
    $this->title = tr('Dashboard');
    $libs = array();
    $files = scandir(LIB_PATH);
    if ($files !== false) {
      foreach ($files as $file) {
        if ($file[0] == '.') {
          continue;
        }
        if (is_dir(LIB_PATH . '/' . $file)) {
          $libs[] = $file;
        }
      }
    }
    $this->libs = $libs;
    return $this->render();
  }
}
