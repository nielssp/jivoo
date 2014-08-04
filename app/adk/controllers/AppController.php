<?php
class AppController extends Controller {
  
  protected $helpers = array('Html', 'Form', 'Admin', 'Icon');
  
  protected function init() {
    $menu = new IconMenu(tr('Main'));
    
    $apps = array();
    $this->appDir = realpath($this->p('app') . '/..');
    $appMenu = array();
    $files = scandir($this->appDir);
    if ($files !== false) {
      foreach ($files as $file) {
        if ($file[0] == '.') {
          continue;
        }
        if (file_exists($this->appDir . '/' . $file . '/app.php')) {
          $app = include $this->appDir . '/' . $file . '/app.php';
          $apps[] = $app;
          $appMenu[$file] = IconMenu::menu($app['name'], null, 'cog', array(
            IconMenu::item(tr('Dashboard')),
            IconMenu::item(tr('Controllers')),
            IconMenu::item(tr('Models')),
            IconMenu::item(tr('Schemas')),
          ));
        }
      }
    }
    $this->apps = $apps;
    ksort($appMenu);
    
    $menu->fromArray(array(
      'status' => IconMenu::menu(tr('Status'), null, null, array(
        IconMenu::item(tr('Dashboard'), 'App::index', 'meter'),
      )),
      'applications' => IconMenu::menu(tr('Applications'), null, null, $appMenu),
      'about' => IconMenu::menu(tr('About'), array(), null, array(
        IconMenu::item(tr('Settings'), 'App::settings', 'wrench'),
        IconMenu::item(tr('Help & support'), 'App::about', 'support'),
        IconMenu::item(tr('About Jivoo'), 'App::about', 'jivoo'),
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
  
  public function settings() {
    $this->title = tr('Settings');
    return $this->render();
  }
  
  public function about() {
    $this->title = tr('About');
    return $this->render();
  }
  
  public function notFound() {
    $this->title = tr('Not found');
    $this->setStatus(404);
    return $this->render();
  }
}
