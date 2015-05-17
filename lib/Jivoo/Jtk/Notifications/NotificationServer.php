<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Notifications;

use Jivoo\Snippets\CometSnippet;
use Jivoo\Core\Json;

class NotificatonServer extends CometSnippet {

  protected $refreshRate = 1.0;

  protected function update() {
    $this->session->open();
    if (isset($this->session['jtk-notifications'])) {
      $notifications = $this->session['jtk-notifications'];
      if (is_array($notifications) and count($notifications) > 0) {
        unset($this->session['jtk-notifications']);
        $this->session->close();
        return Json::encodeResponse($notifications);
      }
    }
    $this->session->close();
    return null;
  }
}
