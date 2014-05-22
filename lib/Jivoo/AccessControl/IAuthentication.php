<?php

interface IAuthentication {
  /**
   * 
   * @param Request $request
   * @param IUserModel $userModel
   * @return ActiveRecord
   */
  public function authenticate($data, IUserModel $userModel, IPasswordHasher $hasher);
  
  public function deauthenticate(IRecord $user, IUserModel $userModel);
  
  public function cookie();
}