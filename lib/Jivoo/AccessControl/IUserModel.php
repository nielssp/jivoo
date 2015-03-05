<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

/**
 * A model that can be used with {@see AuthHelper}.
 */
interface IUserModel {
  /**
   * Find a user matching the provided identification data.
   * @param array $data Identification data, e.g. a username.
   * @return mixed|null User data (e.g. an {@see Jivoo\Models\IBasicRecord}) for
   * an authenticated user or null on failure.
   */
  public function findUser(array $data);
  
  /**
   * Create a session.
   * @param mixed $userData User data, as returned by {@see authenticate()} or
   * {@see openSession()}.
   * @param int $validUntil Time at which session is no longer valid.
   * @return string A session id.
   */
  public function createSession($userData, $validUntil);
  /**
   * Open an existing session, i.e. find the user associated with the session id.
   * @param string $sessionId A session id.
   * @return mixed|null User data (e.g. an {@see Jivoo\Models\IBasicRecord}) or
   * null if session id is invalid.
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