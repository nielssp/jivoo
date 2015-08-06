<?php
namespace Chat\Snippets;

use Jivoo\Snippets\CometSnippet;
use Jivoo\Core\Json;
use Jivoo\Routing\TextResponse;
use Jivoo\Routing\Http;
use Jivoo\Core\Utilities;

class MessageServer extends CometSnippet {

  protected $models = array('Message');

  protected $refreshRate = 1.0;

  protected function update() {
    if (isset($this->request->query['lastMessage'])) {
      $messages = $this->Message->where('id > %i', $this->request->query['lastMessage'])->orderBy('id');
      $response = array();
      foreach ($messages as $message) {
        $response[] = array(
          'id' => $message->id,
          'author' => $message->author,
          'message' => $message->message
        );
      }
      if (count($response) > 0)
        return Json::encodeResponse($response);
    }
    else {
      $messages = $this->Message->orderByDescending('id')->limit(10);
      $response = array();
      foreach ($messages as $message) {
        $response[] = array(
          'id' => $message->id,
          'author' => $message->author,
          'message' => $message->message
        );
      }
      $response = array_reverse($response);
      return Json::encodeResponse($response);
    }
    return null;
  }
}
