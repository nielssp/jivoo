<?php

interface IAuthentication {
  /**
   * 
   * @param Request $request
   * @param IUserModel $userModel
   * @return ActiveRecord
   */
  public function authenticate(Request $request, IUserModel $userModel);
}