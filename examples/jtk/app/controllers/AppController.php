<?php
namespace App\Controllers;

use Jivoo\Controllers\Controller;
use Jivoo\Jtk\ClassIconProvider;

class AppController extends Controller {
  
  protected $helpers = array('Html', 'Jtk', 'Icon', 'Form', 'Skin', 'Css');
  
  public function before() {
    $this->Jtk->setTheme('flatmin');
    $this->Icon->addProvider(new ClassIconProvider());
    
    $options = array();
    if (isset($this->session['color']))
      $options['color'] = $this->session['color'];
    
    $this->Skin->apply('App::skin_css', $options);
  }
  
  public function skin_css() {
    if (isset($this->request->query['color']))
      $this->Skin->primaryBg = $this->request->query['color'];

    $this->cache();
    return $this->render('jivoo/jtk/skin.css');
  }

  public function index() {
    $this->title = tr('Dashboard');
    return $this->render();
  }

  public function colors() {
    $this->title = tr('Colors');
    if ($this->request->hasValidData('color')) {
      $this->session['color'] = key($this->request->data['color']);
      return $this->refresh();
    }
    return $this->render();
  }
  
  public function notFound() {
    $this->title = tr('Not found');
    $this->setStatus(404);
    return $this->render();
  }
}
