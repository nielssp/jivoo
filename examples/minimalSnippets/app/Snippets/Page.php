<?php
namespace Minimal\Snippets;

use Jivoo\Snippets\SnippetBase;

class Page extends SnippetBase {
  public function get() {
    $template = 'pages/' . implode('/', $this->request->path) . '.html';
    if (!file_exists($this->p('app', 'templates/' . $template . '.php')))
      return $this->invalid();
    return $this->render($template);
  }
}