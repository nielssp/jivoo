<?php
abstract class Table extends Model {

  public abstract function setSchema();

  
  public function countSelection(ReadSelection $selection) {
    $result = $selection->select('COUNT(*)');
    return $result[0]['COUNT(*)'];
  }
  
  public function firstSelection(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc());
  }
  
  public function lastSelection(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection->reverseOrder()->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc());
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
}
