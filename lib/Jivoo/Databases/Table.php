<?php
/**
 * A database table.
 */
abstract class Table extends Model {
  /**
   * Set schema of table.
   * @param ISchema $schema Schema.
   */
  public abstract function setSchema(ISchema $schema);

  /**
   * {@inheritdoc}
   */
  public function countSelection(ReadSelection $selection) {
    $result = $selection->select('COUNT(*)');
    return $result[0]['COUNT(*)'];
  }

  /**
   * {@inheritdoc}
   */
  public function firstSelection(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc());
  }

  /**
   * {@inheritdoc}
   */
  public function lastSelection(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection->reverseOrder()->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc());
  }

  /**
   * {@inheritdoc}
   */
  public function read(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection);
    return new ResultSetIterator($this, $resultSet);
  }

  /**
   * {@inheritdoc}
   */
  public function readCustom(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection);
    $result = array();
    while ($resultSet->hasRows()) {
      $result[] = $resultSet->fetchAssoc();
    }
    return $result;
  }

  /**
   * Read a selection.
   * @param ReadSelection $selection A read selection.
   * @return IResultSet A result set.
   */
  public abstract function readSelection(ReadSelection $selection);
}
