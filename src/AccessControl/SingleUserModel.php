<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Routing\Session;
use Jivoo\Core\Utilities;

/**
 * Single-user model. Can be used for simple authentication of a single
 * administration user.
 */
class SingleUserModel implements UserModel {
  /**
   * @var Session Used for storing session data.
   */
  private $session;
  
  /**
   * @var string Username.
   */
  private $username;
  
  /**
   * @var string Password hash.
   */
  private $password;

  /**
   * Construct single-user model.
   * @param Session Used for storing session data.
   * @param string $username Username.
   * @param string $password Password hash.
   */
  public function __construct(Session $session, $username, $password) {
    $this->session = $session;
    $this->username = $username;
    $this->password = $password;
  }
  
  /**
   * {@inheritdoc}
   */
  public function findUser(array $data) {
    if (isset($data['username']) and $data['username'] === $this->username)
      return array('username' => $this->username);
    return null;
  }
  
  /**
   * Get password for user.
   * @param array $userData User data.
   * @return string Passowrd hash.
   */
  public function getPassword($userData) {
    return $this->password;
  }

  /**
   * {@inheritdoc}
   */
  public function createSession($userData, $validUntil) {
    $sessionId = base64_encode(Random::bytes(32));
    $this->session['user_session'] = $sessionId;
    return $sessionId;
  }
  
  /**
   * {@inheritdoc}
   */
  public function openSession($sessionId) {
    if ($this->session['user_session'] === $sessionId)
      return array('username' => $this->username);
    return null;
  }
  
  /**
   * {@inheritdoc}
   */
  public function renewSession($sessionId, $validUntil) {
  }

  /**
   * {@inheritdoc}
   */
  public function deleteSession($sessionId) {
    unset($this->session['user_session']);
  }
}