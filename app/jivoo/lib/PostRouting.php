<?php
class PostRouting extends AppListener {
  
  protected $handlers = array('Routing.beforeRender');

  public function beforeRender() {
    $this->request = $this->m->Routing->getRequest();
    if ($this->config['blog']['fancyPermalinks']) {
      $this->detectPermalink();
      $this->m->Routing->addPath('Posts', 'view', 1, array($this, 'getPermalink'), 'post');
      $this->m->Routing->addPath('Comments', 'index', 1, array($this, 'getPermalink'), 'comments');
      $this->m->Routing->addPath('Comments', 'view', 2, array($this, 'getPermalink'), 'comment');
      $this->detectArchive();
      $this->m->Routing->addPath('Posts', 'archive', 1, array($this, 'getArchiveLink'));
      $this->m->Routing->addPath('Posts', 'archive', 2, array($this, 'getArchiveLink'));
      $this->m->Routing->addPath('Posts', 'archive', 3, array($this, 'getArchiveLink'));
    }
    else {
      $this->m->Routing->addRoute('posts/*', 'Posts::view');
      $this->m->Routing->addRoute('posts/*/comments', 'Comments::index');
      $this->m->Routing->addRoute('posts/*/comments/*', 'Comments::view');
      $this->m->Routing->addRoute('archive/*', 'Posts::archive');
      $this->m->Routing->addRoute('archive/*/*', 'Posts::archive');
      $this->m->Routing->addRoute('archive/*/*/*', 'Posts::archive');
    }
  }
  
  
  
  private function detectArchive() {
    $path = $this->request->path;
    if (!is_array($path))
      return;
    $len = count($path);
    if ($len < 1 or $len > 3)
      return;
    $year = $path[0];
    if (preg_match('/^[0-9]{4}$/', $year) !== 1)
      return;
    if ($len == 1) {
      $this->m->Routing->setRoute(array(
        'controller' => 'Posts',
        'action' => 'archive',
        $year
        ), 6
      );
    }
    else {
      $month = $path[1];
      if (preg_match('/^[0-9]{2}$/', $month) !== 1)
        return;
      if ($len == 2) {
        $this->m->Routing->setRoute(array(
          'controller' => 'Posts',
          'action' => 'archive',
          $year, $month
          ), 6
        );
      }
      else {
        $day = $path[2];
        if (preg_match('/^[0-9]{2}$/', $month) !== 1)
          return;
        $this->m->Routing->setRoute(array(
          'controller' => 'Posts',
          'action' => 'archive',
          $year, $month, $day
          ), 6
        );
      }
    }
  }
  
  private function detectPermalink() {
    $path = $this->request->path;
    $permalink = explode('/', $this->config['blog']['permalink']);
    if (!is_array($path) OR !is_array($permalink)) {
      return;
    }
    $diff = count($path) - count($permalink);
    if ($diff < 0 OR $diff > 2) {
      return;
    }
    if ($diff > 0 AND $path[count($permalink)] != 'comments') {
      return;
    }
    if ($diff == 2 AND preg_match('/^[0-9]+$/', $path[count($path) - 1]) !== 1) {
      return;
    }
    $name = '';
    $id = 0;
    foreach ($permalink as $key => $dir) {
      if (empty($path[$key])) {
        return;
      }
      switch ($dir) {
        case '%year%':
          if (preg_match('/^[0-9]{4}$/', $path[$key]) !== 1) {
            return;
          }
          break;
        case '%month%':
          if (preg_match('/^[0-9]{2}$/', $path[$key]) !== 1) {
            return;
          }
          break;
        case '%day%':
          if (preg_match('/^[0-9]{2}$/', $path[$key]) !== 1) {
            return;
          }
          break;
        case '%name%':
          $name = $path[$key];
          break;
        case '%id%':
          if (preg_match('/^[0-9]+$/', $path[$key]) !== 1) {
            return;
          }
          $id = $path[$key];
          break;
        default:
          if ($dir != $path[$key]) {
            return;
          }
          break;
      }
    }
    if ($id > 0) {
      $post = $this->m->Models->Post->find($id);
    }
    else if (!empty($name)) {
      $post = $this->m->Models->Post->where('name = ?', $name)->first();
    }
    else {
      return;
    }
    if (!isset($post)) {
      return;
    }
    if ($diff == 2) {
      $commentId = $path[count($path) - 1];
      $this->m->Routing->setRoute(array(
        'controller' => 'Comments',
        'action' => 'view',
        'parameters' => array($post->id, $commentId)
        ), 6
      );
    }
    else if ($diff == 1) {
      $this->m->Routing->setRoute(array(
        'controller' => 'Comments',
        'action' => 'index',
        'parameters' => array($post->id)
        ), 6
      );
    }
    else {
      $this->m->Routing->setRoute(array(
        'controller' => 'Posts',
        'action' => 'view',
        'parameters' => array($post->id)
        ), 6
      );
    }
  }
  
  public function getArchiveLink($parameters) {
    $len = count($parameters);
    switch ($len) {
      case 0:
        return array('archive');
      case 1:
        return array(sprintf('%04d', $parameters[0]));
      case 2:
        return array(
          sprintf('%04d', $parameters[0]),
          sprintf('%02d', $parameters[1])
        );
      default:
        return array(
          sprintf('%04d', $parameters[0]),
          sprintf('%02d', $parameters[1]),
          sprintf('%02d', $parameters[2])
        );
    }
  }

  public function getPermalink($parameters, $type = 'post') {
    $permalink = explode('/', $this->config['blog']['permalink']);
    if (is_array($permalink)) {
      if (is_object($parameters) AND is_a($parameters, 'Post')) {
        $record = $parameters;
      }
      else {
        if ($parameters[0] == 0) {
          $record = $this->m->Models->Post->create();
          $record->name = '%name%';
          $record->published = time();
        }
        else {
          $record = $this->m->Models->Post->find($parameters[0]);
          if (!$record)
            return null;
        }
      }
      $time = $record->published;
      $replace = array(
        '%name%' => $record->name,
        '%id%' => (isset($record->id)) ? $record->id : 0,
        '%year%' => tdate('Y', $time), '%month%' => tdate('m', $time),
        '%day%' => tdate('d', $time)
      );
      $search = array_keys($replace);
      $replace = array_values($replace);
      $path = array();
      foreach ($permalink as $dir) {
        $path[] = str_replace($search, $replace, $dir);
      }
      if ($type == 'comments') {
        $path[] = 'comments';
      }
      if ($type == 'comment') {
        $path[] = 'comments';
        $path[] = $parameters[1];
      }
      return $path;
    }
    return null;
  }
}