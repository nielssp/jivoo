<?php
namespace Chat\Snippets;

use Jivoo\Snippets\CometSnippet;

class MessageServer extends CometSnippet {

//   protected $models = array('Message');

  protected $refreshRate = 1.0;

  protected function update() {
//     $messages = $this->Message->where('id > %d', $this->lastMessage);
//     if (/* messages available */) {
//       return /* messages */;
//     }
    return null;
  }
}
