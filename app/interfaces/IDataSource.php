<?php
interface IDataSource {
  public function getName();
  /**
   * @return Schema
   */
  public function getSchema();

  public function setSchema(Schema $schema = NULL);
  /**
   * The C of CRUD (Create).
   * @since 0.3.0
   * @param InsertQuery $query The insert query
   * @return int|InsertQuery The last insert id or a new InsertQuery
   *                         if $query is NULL
   */
  public function insert(InsertQuery $query = NULL);
  /**
   * The R of CRUD (Retrieve).
   * @since 0.3.0
   * @param SelectQuery $query The select query
   * @return int The number of return rows
   */
  public function select(SelectQuery $query = NULL);
  /**
   * The U of CRUD (Update).
   * @since 0.3.0
   * @param UpdateQuery $query The update query
   * @return int The number of affected rows
   */
  public function update(UpdateQuery $query = NULL);
  /**
   * The D of CRUD (Delete).
   * @since 0.3.0
   * @param DeleteQuery $query The delete query
   * @return int The number of affected rows
   */
  public function delete(DeleteQuery $query = NULL);
  public function count(SelectQuery $query = NULL);
}

