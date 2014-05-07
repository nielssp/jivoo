<?php

interface IAuthorization {
  public function authorize(ActiveRecord $user);
}