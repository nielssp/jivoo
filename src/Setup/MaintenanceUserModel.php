<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Core\Store\Config;
use Jivoo\AccessControl\UserModel;
use Jivoo\AccessControl\Random;

/**
 * User model for the maintenance user
 */
class MaintenanceUserModel implements UserModel {
  /**
   * @var Config Lock.
   */
  private $lock;

  /**
   * Construct model.
   * @param Config $lock Maintenance lock.
   */
  public function __construct(Config $lock) {
    $this->lock = $lock;
  }
  
  /**
   * {@inheritdoc}
   */
  public function findUser(array $data) {
    if (isset($data['username']) and $data['username'] === $this->lock->get('username'))
      return array('username' => $this->lock->get('username'));
    return null;
  }
  
  /**
   * Get password.
   * @param array $userData User data.
   * @return string Password hash.
   */
  public function getPassword($userData) {
    return $this->lock->get('password');
  }

  /**
   * {@inheritdoc}
   */
  public function createSession($userData, $validUntil) {
    $sessionId = base64_encode(Random::bytes(32));
    $this->lock['session'] = $sessionId;
    $this->lock->save();
    return $sessionId;
  }
  
  /**
   * {@inheritdoc}
   */
  public function openSession($sessionId) {
    if ($this->lock->get('session') === $sessionId)
      return array('username' => $this->lock->get('username'));
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
    unset($this->lock['session']);
  }
}
