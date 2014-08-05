<?php
class AppController extends Controller {
  
  protected $helpers = array('Html', 'Form', 'Admin', 'Icon');
  
  protected function init() {
    $menu = new IconMenu(tr('Main'));
    
    $this->appDir = realpath($this->p('app') . '/..');
    $files = scandir($this->appDir);
    if ($files !== false) {
      foreach ($files as $file) {
        if ($file[0] == '.')
          continue;
        if (file_exists($this->appDir . '/' . $file . '/app.php'))
          $this->config['applications'][$file] = $this->appDir . '/' . $file . '/app.php';
      }
    }

    $apps = array();
    $appMenu = array();
    foreach ($this->config['applications']->getArray() as $name => $path) {
      if (!file_exists($path)) {
        unset($this->config['applications'][$name]);
        $this->session->flash['warn'][] = tr('%1 no longer exists.', $path);
      }
      else {
        $app = include $path;
        $apps[$name] = $app;
        $route = array('controller' => 'Applications', 'action' => 'dashboard', $name);
        $appMenu[$name] = IconMenu::menu($app['name'], $route, 'cog', array(
          IconMenu::item(tr('Dashboard'), $route),
          IconMenu::item(tr('Controllers'), $route),
          IconMenu::item(tr('Models'), $route),
          IconMenu::item(tr('Schemas'), $route),
        ));
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
        IconMenu::item(tr('Help & support'), 'App::help', 'support'),
        IconMenu::item(tr('About Jivoo'), 'App::about', 'jivoo'),
      )),
    ));
    $this->m->Administration->menu['main'] = $menu;
  }
  
  public function before() {
    $this->view->icon('jivoo-red.ico');
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
  
  public function help() {
    $this->title = tr('Help & support');
    return $this->render();
  }
  
  public function notFound() {
    $this->title = tr('Not found');
    $this->setStatus(404);
    return $this->render();
  }
}
