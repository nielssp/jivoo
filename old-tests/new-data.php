<?php
namespace Jivoo\Models;

use Jivoo\Models\Selection\Selection;
use Jivoo\Models\Selection\DeleteSelection;
use Jivoo\Models\Selection\ReadSelection;

interface Model extends Selection {
  public function getSchema();
  
  public function save(Record $record);
  
  public function saveDeferred(Record $record);
  
  public function commit();
}

interface Selectable {
  
}

interface DataSource {
  public function update(UpdateSelection $selection);

  public function delete(DeleteSelection $selection);
    
  public function read(ReadSelection $selection);
}

abstract class ModelBase {
  public abstract function getSource();
  // implements Selection using ArraySelections or something
}