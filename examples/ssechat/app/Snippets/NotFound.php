<?php
namespace Chat\Snippets;

use Jivoo\Snippets\SnippetBase;

class NotFound extends SnippetBase {
  public function get() {
    $this->setStatus(404);
    return $this->render();
  }
}
