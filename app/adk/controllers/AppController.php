<?php
class AppController extends Controller {
  public function index() {
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
    return $this->render();
  }
}
