<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Core\I18n\I18n;
use Jivoo\Core\Store\Session;
use Jivoo\Core\Store\PhpSessionStore;
use Jivoo\Routing\RequestToken;
use Jivoo\Core\Binary;
use Jivoo\AccessControl\Random;

/**
 * Initializes the session system.
 */
class SessionUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  protected $requires = array('Request');
  
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $store = new PhpSessionStore();
    $app->m->session = new Session($store);
    $app->m->addProperty('session', $app->m->session);
    
    $app->m->request->requestToken = new SessionToken($this->m->session);
  }
  
  /**
   * {@inheritdoc}
   */
  public function stop(App $app, Document $config) {
    $app->m->session->close();
  }
}

class SessionToken implements RequestToken {
  private $session;
  public function __construct(Session $session) {
    $this->session = $session;
  }
  public function getToken() {
    if (!isset($this->session['request_token'])) {
      $this->session['request_token'] = Binary::base64Encode(Random::bytes(32), true);
    }
    return $this->session['request_token'];
  }
}