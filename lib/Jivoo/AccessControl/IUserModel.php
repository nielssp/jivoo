<?php

interface IUserModel extends IModel {
  public function createSession(ActiveRecord $user, $validUntil);
  public function openSession($sessionId);
  public function deleteSession($sessionId);
}