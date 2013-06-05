<?php
interface IDataSource {
  public function getName();
  /**
   * @return Schema
   */
  public function getSchema();

  public function setSchema(Schema $schema = null);
  /**
   * The C of CRUD (Create).
   * @since 0.3.0
   * @param InsertQuery $query The insert query
   * @return int|InsertQuery The last insert id or a new InsertQuery
   *                         if $query is null
   */
  public function insert(InsertQuery $query = null);
  /**
   * The R of CRUD (Retrieve).
   * @since 0.3.0
   * @param SelectQuery $query The select query
   * @return int|SelectQuery The number of returned rows or a new SelectQuery
   *                         if $query is null
   */
  public function select(SelectQuery $query = null);
  /**
   * The U of CRUD (Update).
   * @since 0.3.0
   * @param UpdateQuery $query The update query
   * @return int|UpdateQuery The number of affected rows or a new SelectQuery
   *                         if $query is null
   */
  public function update(UpdateQuery $query = null);
  /**
   * The D of CRUD (Delete).
   * @since 0.3.0
   * @param DeleteQuery $query The delete query
   * @return int|DeleteQuery The number of affected rows or a new SelectQuery
   *                         if $query is null
   */
  public function delete(DeleteQuery $query = null);
  public function count(SelectQuery $query = null);
}

