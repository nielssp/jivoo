<?php
namespace Minimal\Snippets;

use Jivoo\Snippets\Snippet;

class NotFound extends Snippet {
  public function get() {
    $this->setStatus(404);
    return $this->render();
  }
}
