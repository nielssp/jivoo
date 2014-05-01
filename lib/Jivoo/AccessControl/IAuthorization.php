<?php

interface IAuthorization {
  public function authorize(IUser $user);
}