<?php
class PageRouting extends AppListener {
  
  protected $handlers = array('Routing.beforeRender');

  public function beforeRender() {
    $this->request = $this->m->Routing->getRequest();
    $this->detectPermalink();
    $this->m->Routing->addPath('Pages', 'view', 1, array($this, 'getPermalink'));
  }
  
  private function detectPermalink() {
    $path = $this->request->path;
    if (!is_array($path) OR count($path) < 1) {
      return;
    }
    $name = implode('/', $path);
    $page = $this->m->Models->Page->where('name = ?', $name)->first();
    if (!isset($page)) {
      return;
    }
    $this->m->Routing->setRoute(array(
      'controller' => 'Pages',
      'action' => 'view',
      'parameters' => array($page->id)
    ), 6);
  }

  public function getPermalink($parameters) {
    if (is_object($parameters) AND is_a($parameters, 'Page')) {
      $record = $parameters;
    }
    else {
      $record = $this->m->Models->Page->find($parameters[0]);
      if (!$record)
        return false;
    }
    return explode('/', $record->name);
  }
}