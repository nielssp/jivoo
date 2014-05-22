<?php

class AuthorizationRequest {
  private $controller;
  private $action;
  private $user;
  public function __construct(Controller $controller, $action, IRecord $user = null) {
    $this->controller = $controller;
    $this->action = $action;
    $this->user = $user;
  }
  public function __get($property) {
    switch ($property) {
      case 'controller':
      case 'action':
      case 'user':
        return $this->$property;
    }
  }
}