<?php

interface IAuthentication {
  /**
   * 
   * @param Request $request
   * @param IUserModel $userModel
   * @return ActiveRecord
   */
  public function authenticate($data, IUserModel $userModel);
  
  public function cookie();
}