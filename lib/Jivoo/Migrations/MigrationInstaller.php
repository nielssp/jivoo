<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Migrations;

use Jivoo\Setup\InstallerSnippet;
use Jivoo\Setup\AsyncTask;
use Jivoo\Databases\IMigratableDatabase;

class MigrationInstaller extends MigrationUpdater {
  
  private $dbName = 'default'; // TODO: set this somewhere
  
  private $db = null; 
  
  protected function setup() {
    $this->appendStep('check');
    $this->appendStep('clean');
    $this->appendStep('initialize');
    $this->appendStep('migrate');
    $this->appendStep('create');
  }
    
  public function check($data) {
    // if schema_revision exists
    $this->viewData['enableNext'] = false;
    $this->viewData['title'] = tr('Existing data detected');
    if ($this->m->Migrations->isInitialized($this->dbName)) {
      if (isset($data)) {
        if (isset($data['migrate']))
          return $this->jump('migrate');
        else if (isset($data['clean']))
          return $this->jump('clean');
      }
    }
    else {
      if ($this->m->Migrations->isClean($this->dbName))
        return $this->jump('initialize');
      $existing = array();
      $schema = $this->db->getSchema();
      foreach ($schema->getTables() as $table) {
        if (isset($this->db->$table))
          $existing[] = $table;
      }
      if (count($existing) == 0)
        return $this->jump('initialize');
      else if (isset($data) and isset($data['clean']))
        return $this->jump('clean');
      $this->viewData['existing'] = $existing;
    }
    return $this->render();
  }
  
  public function clean($data) {
    $this->m->Migrations->clean($this->dbName);
    return $this->next();
  }
  
  public function initialize($data) {
    $this->m->Migrations->initialize($this->dbName);
    return $this->jump('create');
  }

  public function create($data) {
    $this->viewData['title'] = tr('Creating tables');
    $task = new CreateTask($this->db);
    if ($this->runAsync($task))
      return $this->next();
    return $this->render();
  }
}

class CreateTask extends AsyncTask {
  private $db;
  private $schema;
  
  private $tables = array();
  
  public function __construct(IMigratableDatabase $db) {
    $this->db = $db;
    $this->schema = $db->getSchema();
  }
  
  public function suspend() {
    return array('tables' => $this->tables);
  }
  
  public function resume(array $data) {
    if (isset($data['tables'])) {
      $this->tables = $data['tables'];
    }
    else {
      $this->tables = $this->schema->getTables();
    }
  }
  
  public function isDone() {
    return count($this->tables) == 0;
  }
  
  public function run() {
    $table = array_shift($this->tables);
    if (!isset($this->db->$table)) {
      $this->status(tr('Creating table "%1"...', $table));
      $this->db->createTable($this->schema->getSchema($table));
    }
  }
}