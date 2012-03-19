<?php

class Configuration implements IModule {

  private $errors = NULL;

  public function __construct(Errors $errors) {
    $this->errors = $errors;
  }

  public static function getDependencies() {
    return array('errors');
  }
}