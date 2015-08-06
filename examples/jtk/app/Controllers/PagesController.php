<?php
namespace App\Controllers;

class PagesController extends AppController {

  public function view() {
    $template = 'pages/' . implode('/', $this->request->path) . '.html';
    if (!file_exists($this->p('app', 'templates/' . $template)) and
         !file_exists($this->p('app', 'templates/' . $template . '.php'))) {
      return $this->notFound();
    }
    return $this->render($template);
  }
}