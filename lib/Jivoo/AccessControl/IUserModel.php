<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Models\IModel;
use Jivoo\ActiveModels\ActiveRecord;

/**
 * A model that can be used with {@see AuthHelper}.
 */
interface IUserModel extends IModel {
  /**
   * Create a session.
   * @param ActiveRecord $user A user.
   * @param int $validUntil Time at which session is no longer valid.
   * @return string A session id.
   */
  public function createSession(ActiveRecord $user, $validUntil);
  /**
   * Open an existing session, i.e. find the user associated with the session id.
   * @param string $sessionId A session id.
   * @return ActiveRecord|null A user object or null if invalid session id.
   */
  public function openSession($sessionId);
  
  /**
   * Renew a session.
   * @param string $sessionId A session id.
   * @param int $validUntil Time at which session is no longer valid.
   */
  public function renewSession($sessionId, $validUntil);
  /**
   * Delete a session.
   * @param string $sessionId A session id.
   */
  public function deleteSession($sessionId);
}