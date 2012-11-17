<?php

interface IMigratable {
  public function getSchema($table);

  public function createTable(Schema $schema);

  public function dropTable($table);

  public function addColumn($table, $column, $options = array());

  public function deleteColumn($table, $column);

  public function alterColumn($table, $column, $options = array());

  public function createIndex($table, $index, $options = array());

  public function deleteIndex($table, $index);

  public function alterIndex($table, $index, $options = array());
}
