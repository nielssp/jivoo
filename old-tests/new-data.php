<?php
namespace Jivoo\Models;

use Jivoo\Models\Selection\Selection;

interface Model extends Selection {
  public function getSchema();
  
  public function save(Record $record);
  
  public function saveDeferred(Record $record);
  
  public function commit();
}

class ModelBase {
  public abstract function getSource();
  // implements Selection using ArraySelections or something
}