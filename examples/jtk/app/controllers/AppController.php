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
  
  public function notFound() {
    $this->setStatus(404);
    return $this->render();
  }
}
