<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\Model;
use Jivoo\Models\Selection\ReadSelection;
use Jivoo\Models\ISchema;

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
  public function firstSelection(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc(), $selection);
  }

  /**
   * {@inheritdoc}
   */
  public function lastSelection(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection->reverseOrder()->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc(), $selection);
  }

  /**
   * {@inheritdoc}
   */
  public function read(ReadSelection $selection) {
    $resultSet = $this->readSelection($selection);
    return new ResultSetIterator($this, $resultSet, $selection);
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
