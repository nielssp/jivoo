<?php
namespace Chat\Snippets;

use Jivoo\Snippets\EventSourceSnippet;
use Jivoo\Core\Json;

class MessageServer extends EventSourceSnippet {

  protected $models = array('Message');

  protected $refreshRate = 1.0;

  private $lastMessage = 0;

  public function before() {
    parent::before();
    if (isset($this->session['lastMessage'])) {
      $this->lastMessage = $this->session['lastMessage'];
    }
    else {
      $messages = $this->Message->orderByDescending('id')->limit(10);
      $log = array();
      foreach ($messages as $message) {
        $log[] = array(
          'id' => $message->id,
          'author' => $message->author,
          'message' => $message->message
        );
      }
      $log = array_reverse($log);
      foreach ($log as $message) {
        $this->trigger(Json::encode($message));
        $this->lastMessage = $message['id'];
      }
    }
  }

  public function after($response) {
    $this->session->open();
    $this->session['lastMessage'] = $this->lastMessage;
    $this->session->close();
  }

  protected function update() {
    $messages = $this->Message->where('id > %i', $this->lastMessage)->orderBy('id');
    foreach ($messages as $message) {
      $this->trigger(Json::encode(array(
        'id' => $message->id,
        'author' => $message->author,
        'message' => $message->message
      )));
      $this->lastMessage = $message->id;
    }
  }
}
