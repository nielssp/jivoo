<?php
namespace Jivoo\Models;

use Jivoo\Models\Selection\Selection;

interface Model extends Selection {
  public function getSchema();
  
  public function save(Record $record);
  
  public function saveDeferred(Record $record);
  
  public function commit();
}

interface DataSource {
  public function updateSelection(UpdateSelectionBuilder $selection);

  public function deleteSelection(DeleteSelectionBuilder $selection);
    
  public function readCustom(ReadSelectionBuilder $selection);
  
  public function read(ReadSelectionBuilder $selection);
}

abstract class ModelBase {
  public abstract function getSource();
  // implements Selection using ArraySelections or something
}