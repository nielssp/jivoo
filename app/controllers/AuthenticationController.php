<?php

class AuthenticationController extends ApplicationController {

  protected $helpers = array('Html', 'Form');

  public function setupRoot(Group $rootGroup = null) {
    $this->title = tr('Welcome to PeanutCMS');

    if (!isset($rootGroup)) {
      $rootGroup = Group::first(
        SelectQuery::create()->where('name = "root"'));
      if (!$rootGroup) {
        $rootGroup = Group::create();
        $rootGroup->name = 'root';
        $rootGroup->title = tr('Admin');
        $rootGroup->save();
        $rootGroup->setPermission('*', true);
      }
    }

    if ($this->request
      ->isPost() AND $this->request
          ->checkToken()) {
      $this->user = User::create($this->request
        ->data['user']);
      $this->user
        ->password = $this->m
        ->Shadow
        ->hash($this->user
          ->password);
      $this->user
        ->confirm_password = $this->m
        ->Shadow
        ->hash($this->user
          ->confirm_password);
      if ($this->user
        ->isValid()) {
        $this->user
          ->setGroup($rootGroup);
        $this->user
          ->save();
        $this->config
          ->set('rootCreated', 'yes');
        $this->refresh();
      }
    }
    else {
      $this->user = User::create();
      $this->user
        ->username = 'root';
    }

    $this->render();
  }

}
