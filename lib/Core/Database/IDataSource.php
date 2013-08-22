<?php
/**
 * A data source that supports CRUD, e.g. a database table
 * @package Core\Database
 */
interface IDataSource {
  /**
   * Get name of data source, e.g. table name
   * @return string Name of data source
   */
  public function getName();
  
  /**
   * Get primary key of data source
   * @return string Primary key
   */
  public function getPrimaryKey();
  
  /**
   * Get table schema 
   * @return Schema Schema for data source
   */
  public function getSchema();

  /**
   * Set table schema
   * @param Schema $schema Schema
   */
  public function setSchema(Schema $schema = null);
  
  /**
   * The C of CRUD (Create).
   * @param InsertQuery $query The insert query
   * @return int|InsertQuery The last insert id or a new InsertQuery
   *                         if $query is null
   */
  public function insert(InsertQuery $query = null);
  
  /**
   * The R of CRUD (Retrieve).
   * @param SelectQuery $query The select query
   * @return IResultSet|SelectQuery A result set or a new SelectQuery
   * if $query is null
   */
  public function select(SelectQuery $query = null);
  
  /**
   * The U of CRUD (Update).
   * @param UpdateQuery $query The update query
   * @return int|UpdateQuery The number of affected rows or a new UpdateQuery
   *                         if $query is null
   */
  public function update(UpdateQuery $query = null);
  
  /**
   * The D of CRUD (Delete).
   * @param DeleteQuery $query The delete query
   * @return int|DeleteQuery The number of affected rows or a new DeleteQuery
   *                         if $query is null
   */
  public function delete(DeleteQuery $query = null);
  
  /**
   * Count number of records in data source matching an optional query
   * @param SelectQuery $query Optional query
   * @return int|false Number of records or false if unsuccessful
   */
  public function count(SelectQuery $query = null);
}

