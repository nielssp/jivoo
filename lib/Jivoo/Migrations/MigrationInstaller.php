<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Migrations;

use Jivoo\Setup\InstallerSnippet;
use Jivoo\Setup\AsyncTask;

class MigrationInstaller extends InstallerSnippet {
  protected function setup() {
    $this->appendStep('check');
    $this->appendStep('create');
    $this->appendStep('migrate');
  }
  
  public function before() {
    $this->view->addTemplateDir($this->p('Jivoo\Migrations\Migrations', 'templates'));
  }
  
  public function check($data) {
    // if schema_revision exists
    if (isset($data)) {
      if (isset($data['migrate']))
        return $this->jump('migrate');
      if (isset($data['clean']))
        return $this->jump('clean');
      return $this->next();
    }
    // else
//     return $this->jump('create');
    return $this->render();
  }
  
  public function clean($data) {
    return $this->next();
  }

  public function create($data) {
    $task = new CreateTask();
    if ($this->runAsync($task))
      return $this->end();
    return $this->render();
  }

  public function migrate($data) {
    $task = new MigrateTask();
    if ($this->runAsync($task))
      return $this->end();
    return $this->render();
  }
}

class CreateTask extends AsyncTask {

  public function suspend() {
  
  }
  
  public function resume($data) {
    
  }
  
  public function isDone() {
    return true;
  }
  
  public function run() {
    
  }
}

class MigrateTask extends AsyncTask {

  public function suspend() {
  
  }
  
  public function resume($data) {
    
  }
  
  public function isDone() {
    return true;
  }
  
  public function run() {
    
  }
}