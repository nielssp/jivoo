<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Core\Utilities;
use Jivoo\Core\Config;
use Jivoo\AccessControl\IUserModel;

/**
 * @todo
 */
class MaintenanceUserModel implements IUserModel {
  /**
   * @var Config Lock.
   */
  private $lock;

  /**
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
   */
  public function getPassword($userData) {
    return $this->lock->get('password');
  }

  /**
   * {@inheritdoc}
   */
  public function createSession($userData, $validUntil) {
    $sessionId = Utilities::randomString(32);
    $this->lock['session'] = $sessionId;
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