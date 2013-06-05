<?php
interface IModel {
  public function create($data = array());
  public function all(SelectQuery $query = null);
  public function first(SelectQuery $query = null);
  public function last(SelectQuery $query = null);
  public function count(SelectQuery $query = null);
}

interface IRecord {
  public function __get($property);
  public function __set($property, $value);
  public function getModel();
  public function save();
  public function delete();
  public function isNew();
  public function isSaved();
}

abstract class ActiveModel implements IModel {
  function __construct(IDataSource $dataSource, AppConfig $config) {
    
  }
}

class ActiveRecord implements IRecord {
}