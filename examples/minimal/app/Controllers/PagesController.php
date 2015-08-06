<?php
namespace Minimal\Controllers;

class PagesController extends AppController {
  public function view() {
    $template = 'pages/' . implode('/', $this->request->path) . '.html';
    if (!file_exists($this->p('app', 'templates/' . $template . '.php'))
      and !file_exists($this->p('app', 'templates/' . $template)))
      return $this->notFound();
    return $this->render($template);
  }
}