<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

/**
 * Stores data in PHP sessions.
 */
class PhpSessionStore implements IStore {
  /**
   * @var bool Whether or not session is open.
   */
  private $open = false;

  /**
   * @var bool Whether or not session is mutable.
   */
  private $mutable = false;

  /**
   * @var string Session subkey.
   */
  public $key = null;
  
  /**
   * @var string Session cookie name.
   */
  public $name = null;
  
  /**
   * @var bool Whether to enable Secure flag on session cookie.
   */
  public $secure = false;
  
  /**
   * @var bool Whether to enable HttpOnly flag on session cookie.
   */
  public $httpOnly = false;
  
  /**
   * @var array
   */
  private $data = null;
  
  /**
   * {@inheritdoc}
   */
  public function open($mutable = false) {
    $params = session_get_cookie_params();
    session_set_cookie_params(
      $params['lifetime'],
      $params['path'],
      $params['domain'],
      $this->secure, $this->httpOnly
    );
    session_name($this->name);
    if (!session_start())
      throw new AccessException(tr('Could not start PHP session'));
    $this->open = true;
    $this->mutable = $mutable;
    if (isset($this->key)) {
      $this->data = array();
      if (isset($_SESSION[$this->key]))
        $this->data = $_SESSION[$this->key];  
    }
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
    if (!$this->open)
      return;
    if ($this->mutable) {
      if (isset($this->key))
        $_SESSION[$this->key] = $this->data;
      else
        $_SESSION = $this->data;
    }
    session_write_close();
    $this->open = false;
    $this->mutable = false;
    $this->data = null;
  }

  /**
   * {@inheritdoc}
   */
  public function read() {
    if (isset($this->data))
      return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function write(array $data) {
    if (!$this->open)
      return;
    if (!$this->mutable)
      throw new AccessException(tr('Not mutable'));
    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function isOpen() {
    return $this->open;
  }

  /**
   * {@inheritdoc}
   */
  public function isMutable() {
    return $this->mutable;
  }
}