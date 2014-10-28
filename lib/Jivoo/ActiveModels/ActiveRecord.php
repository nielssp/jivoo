<?php
class ActiveRecord implements IRecord, IActionRecord, ILinkable {
  
  private $data = array();
  
  private $virtualData = array();
  
  private $updatedData = array();

  private $getters = array();
  private $setters = array();

  /**
   * @var ActiveModel
   */
  private $model;
  private $errors = array();
  private $new = false;
  private $saved = true;

  private $associations = array();
  private $associationObjects = array();

  private final function __construct(ActiveModel $model, $data = array(), $allowedFields = null) {
    $this->model = $model;
    $this->data = array_fill_keys($model->getNonVirtualFields(), null);
    $this->virtualData = array_fill_keys($model->getVirtualFields(), null);
    $this->addData($model->getDefaults());
    $this->addData($data, $allowedFields);
    $this->associations = $this->model->getAssociations();
    $this->getters = $this->model->getGetters();
    $this->setters = $this->model->getSetters();
  }

  public static function createNew(ActiveModel $model, $data = array(), $allowedFields = null, $class = null) {
    if (isset($class))
      $record = new $class($model, $data, $allowedFields);
    else
      $record = new ActiveRecord($model, $data, $allowedFields);
    $record->new = true;
    $record->saved = false;
    $model->triggerEvent('afterCreate', new ActiveModelEvent($record));
    return $record;
  }
  
  public static function createExisting(ActiveModel $model, $data = array(), $class = null) {
    if (isset($class))
      $record = new $class($model, $data);
    else
      $record = new ActiveRecord($model, $data);
    $record->updatedData = array();
    $model->addToCache($record);
    $model->triggerEvent('afterLoad', new ActiveModelEvent($record));
    return $record;
  }

  public function getModel() {
    return $this->model;
  }
  
  public function addData($data, $allowedFields = null) {
    assume(is_array($data));
    if (!isset($allowedFields))
      $allowedFields = $this->model->getFields();
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    foreach ($data as $field => $value) {
      $this->__set($field, $data[$field]);
    }
  }
  
  public function getData() {
    return $this->data;
  }

  public function __get($field) {
    if (isset($this->getters[$field]))
      return call_user_func(array($this->model, $this->getters[$field]), $this);
    if (isset($this->associations[$field])) {
      if (!array_key_exists($field, $this->associationObjects))
        $this->associationObjects[$field] = $this->model
          ->getAssociation($this, $this->associations[$field]);
      return $this->associationObjects[$field];
    }
    if (array_key_exists($field, $this->data))
      return $this->data[$field];
    if (array_key_exists($field, $this->virtualData))
      return $this->virtualData[$field];
    throw new InvalidPropertyException(tr('Invalid property: %1', $field));
  }

  public function __set($field, $value) {
    if (isset($this->setters[$field]))
      call_user_func(array($this->model, $this->setters[$field]), $this, $value);
    else if (isset($this->associations[$field])) {
      $this->model->setAssociation($this, $this->associations[$field], $value);
    }
    else if (array_key_exists($field, $this->data)) {
      $oldValue = $this->data[$field];
      $this->data[$field] = $value;
      if ($oldValue !== $value)
        $this->updatedData[$field] = $value;
      $this->saved = false;
    }
    else if (array_key_exists($field, $this->virtualData))
      $this->virtualData[$field] = $value;
    else
      throw new InvalidPropertyException(tr('Invalid property: %1', $field));
  }

  public function __isset($field) {
    if (isset($this->getters[$field])) {
      $value = call_user_func(array($this->model, $this->getters[$field]), $this);
      return isset($value);
    }
    if (isset($this->associations[$field]))
      return $this->model->hasAssociation($this, $this->associations[$field]);
    if (array_key_exists($field, $this->data))
      return isset($this->data[$field]);
    if (array_key_exists($field, $this->virtualData))
      return isset($this->virtualData[$field]);
    throw new InvalidPropertyException(tr('Invalid property: %1', $field));
  }

  public function __unset($field) {
    if (isset($this->setters[$field]))
      call_user_func(array($this->model, $this->setters[$field]), $this, null);
    else if (isset($this->associations[$field]))
      $this->model->unsetAssociation($this, $this->associations[$field]);
    else if (array_key_exists($field, $this->data)) {
      $this->data[$field] = null;
      $this->updatedData[$field] = null;
      $this->saved = false;
    }
    else if (array_key_exists($field, $this->virtualData)) {
      $this->virtualData[$field] = null;
    }
    else
      throw new InvalidPropertyException(tr('Invalid property: %1', $field));;
  }

  public function __call($method, $parameters) {
    $method = 'record' . ucfirst($method);
    $function = array($this->model, $method);
    array_unshift($parameters, $this);
    if (is_callable($function))
      return call_user_func_array($function, $parameters);
    throw new InvalidMethodException(tr('Invalid method: %1', $method));
  }

  public function set($field, $value) {
    $this->__set($field, $value);
    return $this;
  }

  public function hasChanged($field) {
    return array_key_exists($field, $this->updatedData);
  }

  public function isSaved() {
    return $this->saved;
  }
  
  public function isNew() {
    return $this->new;
  }
  
  public function getErrors() {
    return $this->errors;
  }
  
  public function isValid() {
    $this->model->triggerEvent('beforeValidate', new ActiveModelEvent($this));
    $validator = $this->model->getValidator();
    $this->errors = $validator->validate($this);
    $this->model->triggerEvent('afterValidate', new ActiveModelEvent($this));
    return count($this->errors) == 0;
  }

  public function getRoute() {
    return $this->model->getRoute($this);
  }

  public function action($action) {
    return $this->model->getAction($this, $action);
  }
  
  public function save($validate = true) {
    if ($validate and !$this->isValid())
      return false;
    $this->model->triggerEvent('beforeSave', new ActiveModelEvent($this));
    if ($this->isNew()) {
      foreach ($this->data as $field => $value)
        $this->data[$field] = $this->model->getType($field)->convert($value);
      $insertId = $this->model->insert($this->data);
      $pk = $this->model->getAiPrimaryKey();
      if (isset($pk))
        $this->data[$pk] = $insertId;
      $this->model->addToCache($this);
      $this->new = false;
    }
    else if (count($this->updatedData) > 0) {
      foreach ($this->updatedData as $field => $value) {
        $value = $this->model->getType($field)->convert($value);
        $this->data[$field] = $value;
        $this->updatedData[$field] = $value;
      }
      $this->model->selectRecord($this)->set($this->updatedData)->update();
    }
    $this->updatedData = array();
    $this->saved = true;
    $this->model->triggerEvent('afterSave', new ActiveModelEvent($this));
    return true;
  }
  
  public function delete() {
    $this->model->triggerEvent('beforeDelete', new ActiveModelEvent($this));
    $this->model->selectRecord($this)->delete();
  }

  public function offsetExists($field) {
    return $this->__isset($field);
  }

  public function offsetGet($field) {
    return $this->__get($field);
  }

  public function offsetSet($field, $value) {
    $this->__set($field, $value);
  }

  public function offsetUnset($field) {
    $this->__unset($field);
  }
}

