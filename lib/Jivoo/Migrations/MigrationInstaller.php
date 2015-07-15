<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Migrations;

use Jivoo\Setup\InstallerSnippet;
use Jivoo\Setup\AsyncTask;
use Jivoo\Databases\IMigratableDatabase;

/**
 * Migration installer. Checks database, cleans/migrates data, creates tables.
 */
class MigrationInstaller extends MigrationUpdater {
  /**
   * {@inheritdoc}
   */
  protected function setup() {
    $this->appendStep('check');
    $this->appendStep('clean');
    $this->appendStep('initialize');
    $this->appendStep('migrate');
    $this->appendStep('create');
  }
    
  /**
   * Installer step: Check state of database and allow user to either migrate,
   * clean or initialize.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function check($data = null) {
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

  /**
   * Installer step: Clean database (delete all tables),
   * clean or initialize.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function clean($data = null) {
    $this->m->Migrations->clean($this->dbName);
    return $this->next();
  }

  /**
   * Installer step: Initialize database (create SchemaRevision),
   * clean or initialize.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function initialize($data = null) {
    $this->m->Migrations->initialize($this->dbName);
    return $this->jump('create');
  }

  /**
   * Installer step: Create missing tables.
   * clean or initialize.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function create($data = null) {
    $this->viewData['title'] = tr('Creating tables');
    $task = new CreateTask($this->db);
    if ($this->runAsync($task))
      return $this->next();
    return $this->render();
  }
}

/**
 * Asynchronous task for creating tables.
 */
class CreateTask extends AsyncTask {
  /*
   * @var IMigratableDatabase
   */
  private $db;
  
  /**
   * @var \Jivoo\Databases\DatabaseSchema
   */
  private $schema;
  
  /**
   * @var string[]
   */
  private $tables = array();
  
  /**
   * Construct task.
   * @param IMigratableDatabase $db Database to create tables in.
   */
  public function __construct(IMigratableDatabase $db) {
    $this->db = $db;
    $this->schema = $db->getSchema();
  }
  
  /**
   * {@inheritdoc}
   */
  public function suspend() {
    return array('tables' => $this->tables);
  }

  /**
   * {@inheritdoc}
   */
  public function resume(array $data) {
    if (isset($data['tables'])) {
      $this->tables = $data['tables'];
    }
    else {
      $this->tables = $this->schema->getTables();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isDone() {
    return count($this->tables) == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    $table = array_shift($this->tables);
    if (!isset($this->db->$table)) {
      $this->status(tr('Creating table "%1"...', $table));
      $this->db->createTable($this->schema->getSchema($table));
    }
  }
}
