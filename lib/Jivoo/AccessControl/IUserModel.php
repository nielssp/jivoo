<?php

interface IUserModel extends IModel {
  /**
   * Create a session
   * @param ActiveRecord $user
   * @param integer $validUntil
   * @return string A session id
   */
  public function createSession(ActiveRecord $user, $validUntil);
  /**
   * 
   * @param string $sessionId
   * @return ActiveRecord|null A user object or null if invalid Session
   */
  public function openSession($sessionId);
  
  /**
   * @param string $sessionId
   * @param integer $validUntil
   */
  public function renewSession($sessionId, $validUntil);
  /**
   * 
   * @param string $sessionId
   */
  public function deleteSession($sessionId);
}