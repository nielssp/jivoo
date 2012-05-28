<?php
// Module
// Name           : Extensions
// Version        : 0.2.0
// Description    : The PeanutCMS extension system
// Author         : PeanutCMS
// Dependencies   : errors configuration database routes templates http
//                  users backend

/*
 * Extension system
 *
 * @package PeanutCMS
 */

/**
 * Extensions class
 */
class Extensions implements IModule {

  private $core;
  private $errors;
  private $configuration;
  private $database;
  private $routes;
  private $templates;
  private $http;
  private $users;
  private $backend;

  public function __construct(Core $core) {
    $this->core = $core;
    $this->database = $this->core->database;
    $this->actions = $this->core->actions;
    $this->routes = $this->core->routes;
    $this->http = $this->core->http;
    $this->templates = $this->core->templates;
    $this->errors = $this->core->errors;
    $this->configuration = $this->core->configuration;
    $this->users = $this->core->users;
    $this->backend = $this->core->backend;

    if (!$this->configuration->exists('extensions.installed')) {
      $this->configuration->set('extensions.installed', '');
      $preinstall = explode(' ', PREINSTALL_EXTENSIONS);
      foreach ($preinstall as $extension) {
        if (!empty($extension)) {
          $this->install($extension);
        }
      }
    }

    $this->backend->addLink('settings', 'extensions', tr('Extensions'), array(), 2);
  }

  public function install($extension) {
  }

}
