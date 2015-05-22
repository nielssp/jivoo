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
    $this->appendStep('clean');
    $this->appendStep('create');
    $this->appendStep('migrate');
  }
  
  public function before() {
    $this->view->addTemplateDir($this->p('Jivoo\Migrations\Migrations', 'templates'));
    
    $this->app->getModule('Migrations');
  }
  
  public function check($data) {
    // if schema_revision exists
    $this->viewData['enableNext'] = false;
    $this->viewData['title'] = tr('Existing data detected');
    $db = $this->m->Databases->default->getConnection();
    if (isset($db->SchemaRevision)) {
      if (isset($data)) {
        if (isset($data['migrate']))
          return $this->jump('migrate');
        else if (isset($data['clean']))
          return $this->jump('clean');
      }
    }
    else {
      $existing = array();
      $schema = $db->getSchema();
      foreach ($schema->getTables() as $table) {
        if (isset($db->$table))
          $existing[] = $table;
      }
      if (count($existing) == 0)
        return $this->jump('create');
      else if (isset($data) and isset($data['clean']))
        return $this->jump('clean');
      $this->viewData['existing'] = $existing;
    }
    return $this->render();
  }
  
  public function clean($data) {
    foreach ($dbs as $db) {
      foreach ($schema->getTables() as $table) {
        if (isset($db->$table)) {
          $db->dropTable($table);
        }
      }
    }
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
  
  public function resume(array $data) {
    
  }
  
  public function isDone() {
    return true;
  }
  
  public function run() {
    $this->status(tr('Creating table "%1"...', $table));
  }
}

class MigrateTask extends AsyncTask {

  public function suspend() {
  
  }
  
  public function resume(array $data) {
    
  }
  
  public function isDone() {
    return true;
  }
  
  public function run() {
    $this->status(tr('Running migration "%1"...', $migration));
  }
}