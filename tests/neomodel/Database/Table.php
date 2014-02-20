<?php
abstract class Table extends Model {

  public function update(UpdateSelection $selection = null) {
    if (!isset($selection))
      $selection = new UpdateSelection($this);
    return updateSelection($selection);
  }

  public function delete(DeleteSelection $selection = null) {
    if (!isset($selection))
      $selection = new DeleteSelection($this);
    return updateSelection($selection);
  }
  
  public function count(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    $result = $selection->select('COUNT(*)');
    return $result[0]['COUNT(*)'];
  }
  
  public function first(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    $resultSet = $this->readSelection($selection->limit(1));
    return ActiveRecord::createExisting($this, $resultSet->fetchAssoc());
  }
  
  public function last(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    $resultSet = $this->readSelection($selection->reverseOrder()->limit(1));
    return ActiveRecord::createExisting($this, $resultSet->fetchAssoc());
  }

  public function read(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection);
    return new ResultSetIterator($this, $resultSet);
  }
  
  public function readCustom(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection);
    $result = array();
    while ($resultSet->hasRows()) {
      $result[] = $resultSet->fetchAssoc();
    }
    return $result;
  }

  /**
   * @param ReadSelection $selection
   * @return IResultSet
   */
  public abstract function readSelection(ReadSelection $selection);
  /**
   * @param UpdateSelection $selection
   * @return int Number of affected records
  */
  public abstract function updateSelection(UpdateSelection $selection);
  /**
   * @param DeleteSelection $selection
   * @return int Number of affected records
  */
  public abstract function deleteSelection(DeleteSelection $selection);
}