<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Migrations;

use Jivoo\Setup\InstallerSnippet;
use Jivoo\Setup\AsyncTask;
use Jivoo\Databases\IMigratableDatabase;

class MigrationUpdater extends InstallerSnippet {
  
  protected $dbName = 'default'; // TODO: set this somewhere
  
  protected $db = null; 
  
  protected function setup() {
    $this->appendStep('migrate');
  }
  
  public function before() {
    $this->app->getModule('Migrations');
    
    $name = $this->dbName;
    $this->db = $this->m->Databases->$name->getConnection();
  }

  public function migrate($data) {
    $this->viewData['title'] = tr('Migrating database');
    $task = new MigrateTask($this->m->Migrations, $this->dbName);
    if ($this->runAsync($task)) {
      $this->m->Migrations->finalize($this->dbName);
      return $this->next();
    }
    return $this->render();
  }
}

class MigrateTask extends AsyncTask {

  private $migrations;
  private $name;
  private $missing = array();
  
  public function __construct(Migrations $migrations, $dbName) {
    $this->migrations = $migrations;
    $this->name = $dbName;
  }
  
  public function suspend() {
    return array('missing' => $this->missing);
  }
  
  public function resume(array $data) {
    if (isset($data['waiting'])) {
      $this->missing = $data['missing'];
    }
    else {
      $this->missing = $this->migrations->check($this->name);
    } 
  }
  
  public function isDone() {
    return count($this->missing) == 0;
  }
  
  public function run() {
    $migration = array_shift($this->missing);
    $this->status(tr('Running migration "%1"...', $migration));
    $this->migrations->run($this->name, $migration);
  }
}