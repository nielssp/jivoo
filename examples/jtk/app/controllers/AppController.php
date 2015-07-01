<?php
namespace Minimal\Controllers;

use Jivoo\Controllers\Controller;
use Jivoo\Jtk\ClassIconProvider;

class AppController extends Controller {
  
  protected $helpers = array('Html', 'Jtk', 'Icon');
  
  public function before() {
    $this->Jtk->setTheme('flatmin-base');
    $this->Icon->addProvider(new ClassIconProvider());
  }

  public function index() {
    $this->title = tr('Dashboard');
    return $this->render();
  }

  public function colors() {
    $this->title = tr('Colors');
    return $this->render();
  }
  
  public function notFound() {
    $this->title = tr('Not found');
    $this->setStatus(404);
    return $this->render();
  }
}
