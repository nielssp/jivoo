<?php

abstract class ActiveModel extends Model {

  protected $table = null;

  protected $record = null;

  protected $hasMany = array();

  protected $hasAndBelongsToMany = array();

  protected $belongsTo = array();

  protected $hasOne = array();

  protected $validate = array();

  protected $labels = array();

  protected $mixins = array();

  /**
   * @var Table
   */
  private $source;
  /**
   * @var IDatabase
   */
  private $database;
  
  private $name;
  
  /**
   * @var Schema
   */
  private $schema;
  
  private $validator;
  
  public final function __construct(IDatabase $database) {
    $this->database = $database;
    $this->name = get_class($this);
    if (!isset($this->table))
      $this->table = $this->name;
    $table = $this->table;
    $this->source = $this->database->$table;
    $this->schema = $this->source->getSchema();
    $this->validator = new Validator($this, $this->validate);
    if (isset($this->record)) {
      if (!class_exists($this->record) or !is_subclass_of($this->record, 'ActiveRecord'))
        throw new InvalidRecordClassException(tr(
          'Record class %1 must exist and extend %2', $this->record, 'ActiveRecord'
        ));
    }
  }

  public function create($data = array(), $allowedFields = null) {
    return ActiveRecord::createNew($this, $data, $allowedFields, $this->record);
  }
  
  public function createExisting($data = array()) {
    return ActiveRecord::createExisting($this, $data, $this->record);
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function getSchema() {
    return $this->schema;
  }
  
  public function getValidator() {
    return $this->validator;
  }

  public function getLabel($field) {
    if (isset($this->labels[$field]))
      return $this->labels[$field];
    return ucfirst($field);
  }
  
  public function update(UpdateSelection $selection = null) {
    if (!isset($selection))
      $selection = new UpdateSelection($this);
    return $this->source->update($selection);
  }
  
  public function delete(DeleteSelection $selection = null) {
    if (!isset($selection))
      $selection = new DeleteSelection($this);
    return $this->source->delete($selection);
  }
  
  public function count(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    return $this->source->count($selection);
  }
  
  public function first(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    $resultSet = $this->source->readSelection($selection->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc());
  }
  
  public function last(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    $resultSet = $this->source->readSelection($selection->reverseOrder()->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc());
  }

  public function read(ReadSelection $selection) {
    $resultSet = $this->source->readSelection($selection);
    return new ResultSetIterator($this, $resultSet);
  }

  public function readCustom(ReadSelection $selection) {
    return $this->source->readCustom($selection);
  }
  
  public function insert($data) {
    $this->source->insert($data);
  }
}

class InvalidRecordClassException extends Exception { } 
