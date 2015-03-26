<?php
namespace Chat\Snippets;

use Jivoo\Snippets\Snippet;
use Jivoo\Core\Json;

class SendMessage extends Snippet {
  protected $helpers = array('Form');
  
  protected $models = array('Message');
  
  protected $dataKey = 'Message';
  
  public function post($data) {
    if (!$this->request->accepts('json'))
      return $this->invalid();
    $message = $this->Message->create(
      $data, array('message')
    );
    if ($message->save()) {
      return Json::encodeResponse('success');
    }
    return Json::encodeResponse($message->getErors());
  }
  
  public function get() {
    $this->view->data->message = $this->Message->create();
    return $this->render();
  }
}
