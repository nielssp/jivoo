<?php
abstract class ActiveRecord {
  private static $dbConnection = NULL;
  private static $models = array();
  public static function addModel($class, $table) {
    $db = self::connection();
    if ($db->tableExists($table)) {
      self::$models[$class] = array(
        'table' => $table,
        'columns' => $db->getColumns($table),
        'primaryKey' => $db->getPrimaryKey($table)
      );
    }
    else {
      throw new TableNotFoundException(tr('The table "%1" does not exist.', $table));
    }
  }
  private static $cache = array();

  private $table;
  private $primaryKey;
  private $data;
  private $changed;

  private $isNew = FALSE;
  private $isSaved = TRUE;
  private $isDeleted = FALSE;

  protected $validate = array();

  protected $defaults = array();

  protected $hasOne = array();
  protected $hasMany = array();
  protected $belongsTo = array();
  protected $hasAndBelongsToMany = array();

  private $associations = array();

  private $errors = array();

  public function __set($property, $value) {
    if (array_key_exists($property, $this->data) AND $property != $this->primaryKey) {
      $this->data[$property] = $value;
      $this->changed[$property] = TRUE;
      $this->isSaved = FALSE;
    }
    else {
      throw new RecordPropertyNotFoundException(
        tr('Property "%1" was not found in model "%2".', $property, get_class($this))
      );
    }
  }

  public function __get($property) {
    if (array_key_exists($property, $this->data)) {
      return $this->data[$property];
    }
    else {
      throw new RecordPropertyNotFoundException(
        tr('Property "%1" was not found in model "%2".', $property, get_class($this))
      );
    }
  }

  public function __call($method, $arguments) {
    if (isset($this->associations[$method])) {
      $association = $this->associations[$method][0];
      $procedure = $this->associations[$method][1];
      $assocProc = $association . '_' . $procedure;
      $identifier = $this->associations[$method][2];
      $options = $this->$association;
      $options = $options[$identifier];
      if (!isset($options['class'])) {
        $options['class'] = $identifier;
      }
      $otherClass = $options['class'];
      if (!isset($options['thisKey'])) {
        $options['thisKey'] = strtolower(get_class($this)) . '_id';
      }
      if (!isset($options['otherKey'])) {
        $options['otherKey'] = strtolower($identifier) . '_id';
      }

      if ($association == 'hasAndBelongsToMany' AND !isset($options['join'])) {
        if (strcmp($this->table, self::$models[$otherClass]['table']) < 0) {
          $options['join'] = $this->table . '_' . self::$models[$otherClass]['table'];
        }
        else {
          $options['join'] = self::$models[$otherClass]['table'] . '_' . $this->table;
        }
        // if table does not exist
      }

      if ($association == 'hasMany' OR $association == 'hasAndBelongsToMany') {
        if ($this->isNew) {
          return FALSE;
        }
        switch ($procedure) {
          case 'get':
            return $this->manyGet($options, $arguments[0]);
          case 'has':
            return $this->manyHas($options, $arguments[0]);
          case 'add':
            return $this->manyAdd($options, $arguments[0]);
          case 'remove':
            return $this->manyRemove($options, $arguments[0]);
        }
      }
      else if ($association ==  'hasOne' OR $association == 'belongsTo') {
        if (!isset($options['connection'])) {
//           $options['connection'] = 'this';
          if ($association == 'hasOne') {
            $options['connection'] = 'other';
          }
          else {
            $options['connection'] = 'this';
          }
        }
        if ($this->isNew AND $options['connection'] == 'other') {
          return FALSE;
        }
        switch ($procedure) {
          case 'get':
            return $this->oneGet($options);
          case 'set':
            return $this->oneSet($options, $arguments[0]);
        }
      }
    }
    else {
      throw new RecordMethodNotFoundException(
        tr('Method "%1" was not found in model "%2".', $method, get_class($this))
      );
    }
  }

  private function manyGet($options, SelectQuery $customSelect = NULL) {
    $db = self::connection();
    $thisClass = get_class($this);
    $otherClass = $options['class'];

    $otherPrimaryKey = self::$models[$otherClass]['primaryKey'];

    if (!isset($customSelect)) {
      $select = SelectQuery::create();
    }
    else {
      $select = $customSelect;
    }
    if (isset($options['join'])) {
      $select->join($options['join'], $otherPrimaryKey, $options['otherKey']);
    }

    $select->from(self::$models[$otherClass]['table']);
    $select->where($options['thisKey'] . ' = ?');
    $select->addVar($this->data[$this->primaryKey]);

    $result = $db->executeSelect($select);
    if (isset($options['count']) AND !isset($customSelect)) {
      if ($this->data[$options['count']] != $result->count()) {
        $this->data[$options['count']] = $result->count();
        $this->isSaved = FALSE;
        $this->save();
      }
    }
    $allArray = array();
    while ($assoc = $result->fetchAssoc()) {
      $allArray[] = self::createFromAssoc($otherClass, $assoc);
    }
    return $allArray;
  }

  private function manyHas($options, ActiveRecord $record) {
    $db = self::connection();
    $thisClass = get_class($this);
    $otherClass = $options['class'];

    $otherPrimaryKey = self::$models[$otherClass]['primaryKey'];

    $query = $db->selectQuery();
    if (isset($options['join'])) {
      $query->where($options['thisKey'] . ' = ? AND ' . $options['otherKey'] . ' = ?');
      $query->addVar($this->data[$this->primaryKey]);
      $query->addVar($record->data[$record->primaryKey]);
      return $db->count($options['join'], $query) > 0;
    }
    else {
      $query->where($options['thisKey'] . ' = ?');
      $query->addVar($this->data[$this->primaryKey]);
      return $db->count($record->table, $query) > 0;
    }
  }

  private function manyAdd($options, ActiveRecord $record) {
    $db = self::connection();
    $thisClass = get_class($this);
    $otherClass = $options['class'];

    if ($this->manyHas($options, $record)) {
      return FALSE;
    }

    if (isset($options['join'])) {
      if ($record->isNew) {
        return FALSE;
      }
      $query = $db->insertQuery($options['join']);
      $query->addPair($options['thisKey'], $this->data[$this->primaryKey]);
      $query->addPair($options['otherKey'], $record->data[$record->primaryKey]);
      $query->execute();
      return TRUE;
    }
    else {
      $record->data[$options['thisKey']] = $this->data[$this->primaryKey];
      $record->isSaved = FALSE;
      if (!$record->isNew) {
        $record->save();
      }
      return TRUE;
    }

  }

  private function manyRemove($options, ActiveRecord $record) {
    $db = self::connection();
    $thisClass = get_class($this);
    $otherClass = $options['class'];

    if (isset($options['join'])) {
      if ($record->isNew) {
        return FALSE;
      }
      $query = $db->deleteQuery($options['join']);
      $query->where($options['thisKey'] . ' = ? AND ' . $options['otherKey'] . ' = ?');
      $query->addVar($this->data[$this->primaryKey]);
      $query->addVar($record->data[$record->primaryKey]);
      $query->execute();
      return TRUE;
    }
    else {
      $record->data[$options['thisKey']] = 0;
      $record->isSaved = FALSE;
      if (!$record->isNew) {
        $record->save();
      }
      return TRUE;
    }
  }

  private function oneGet($options) {
    $db = self::connection();
    $thisClass = get_class($this);
    $otherClass = $options['class'];

    if ($options['connection'] == 'this') {
      if (!isset($this->data[$options['otherKey']])) {
        return FALSE;
      }
      $primaryKey = $this->data[$options['otherKey']];
      if (isset(self::$cache[$otherClass][$primaryKey])) {
        return self::$cache[$otherClass][$primaryKey];
      }
      $query = $db->selectQuery(self::$models[$otherClass]['table']);
      $query->where(self::$models[$otherClass]['primaryKey'] . ' = ?');
    }
    else {
      $primaryKey = $this->data[$this->primaryKey];
      $query = $db->selectQuery(self::$models[$otherClass]['table']);
      $query->where($options['thisKey'] . ' = ?');
    }
    $query->addVar($primaryKey);
    $query->limit(1);
    $result = $db->execute($query);
    if (!$result->hasRows()) {
      return FALSE;
    }
    return self::createFromAssoc($class, $result->fetchAssoc());
  }

  private function oneSet($options, ActiveRecord $record) {
    $db = self::connection();
    $thisClass = get_class($this);
    $otherClass = $options['class'];

    if ($options['connection'] == 'this') {
      if ($record->isNew) {
        return FALSE;
      }
      $this->data[$options['otherKey']] = $record->data[$record->primaryKey];
      $this->isSaved = FALSE;
      if (!$this->isNew) {
        $this->save();
      }
    }
    else {
      $record->data[$options['thisKey']] = $this->data[$this->primaryKey];
      $record->isSaved = FALSE;
      if (!$record->isNew) {
        $record->save();
      }
    }
  }

  private function __construct() {
    $db = self::connection();
    $class = get_class($this);
    if (!isset(self::$models[$class])) {
      throw new InvalidModelException(tr('The model "%1" has not been added to ActiveRecord.', $class));
    }
    $this->table = self::$models[$class]['table'];
    $this->primaryKey = self::$models[$class]['primaryKey'];
    $this->data = array();
    foreach (self::$models[$class]['columns'] as $column) {
      $this->data[$column] = NULL;
    }
    foreach ($this->hasOne as $class => $options) {
      $this->associations['get' . $class] = array('hasOne', 'get', $class);
      $this->associations['set' . $class] = array('hasOne', 'set', $class);
    }
    foreach ($this->hasMany as $class => $options) {
      if (!isset($options['plural'])) {
        $options['plural'] = $class . 's';
      }
      $this->associations['get' . $options['plural']] = array('hasMany', 'get', $class);
      $this->associations['has' . $class] = array('hasMany', 'has', $class);
      $this->associations['add' . $class] = array('hasMany', 'add', $class);
      $this->associations['remove' . $class] = array('hasMany', 'remove', $class);
    }
    foreach ($this->belongsTo as $class => $options) {
      $this->associations['get' . $class] = array('belongsTo', 'get', $class);
      $this->associations['set' . $class] = array('belongsTo', 'set', $class);
    }
    foreach ($this->hasAndBelongsToMany as $class => $options) {
      if (!isset($options['plural'])) {
        $options['plural'] = $class . 's';
      }
      $this->associations['get' . $options['plural']] = array('hasAndBelongsToMany', 'get', $class);
      $this->associations['has' . $class] = array('hasAndBelongsToMany', 'has', $class);
      $this->associations['add' . $class] = array('hasAndBelongsToMany', 'add', $class);
      $this->associations['remove' . $class] = array('hasAndBelongsToMany', 'remove', $class);
    }
  }

  protected static function connection() {
    if (!isset(self::$dbConnection)) {
      throw new DatabaseNotConnectedException('ActiveRecord is not connected to a database.');
    }
    return self::$dbConnection;
  }

  public static function connect(IDatabase $db) {
    self::$dbConnection = $db;
  }

  public static function isConnected() {
    return isset(self::$dbConnection);
  }

  private static function createFromAssoc($class, $assoc) {
    $new = new $class();
    foreach ($assoc as $property => $value) {
      if (in_array($property, self::$models[$class]['columns'])) {
        $new->data[$property] = $value;
      }
    }
    return $new;
  }

  public static function create($data = array()) {
    $db = self::connection();
    $class = get_called_class();
    $new = new $class();
    $new->isNew = TRUE;
    $new->isSaved = FALSE;
    $data = array_merge($new->defaults, $data);
    foreach ($data as $property => $value) {
      $new->data[$property] = $value;
    }
    return $new;
  }

  protected function validateValue($value, $conditionKey, $conditionValue) {
    $validate = array();
    switch ($conditionKey) {
      case 'presence':
        return empty($value) != $conditionValue;
      case 'minLength':
        return strlen($value) >= $conditionValue;
      case 'maxLength':
        return strlen($value) <= $conditionValue;
      case 'isNumeric':
        return is_numeric($value) == $conditionValue;
      case 'isInteger':
        return (preg_match('/\A[+-]?\d+\Z/', $value) == 1) == $conditionValue;
      case 'minValue':
        $value = is_float($conditionValue) ? (float) $value : (int) $value;
        return $value >= $conditionValue;
      case 'maxValue':
        $value = is_float($conditionValue) ? (float) $value : (int) $value;
        return $value <= $conditionValue;
      case 'match':
        echo 'preg_match(' . $conditionValue . ', ' . $value .') = ' . preg_match($conditionValue, $value) .'<br/>';
        return preg_match($conditionValue, $value) == 1;
      case 'custom':
        return !is_callable($conditionValue) OR call_user_func($conditionValue, $value);
    }
    return true;
  }

  public function isValid() {
    $this->errors = array();
    foreach ($this->data as $column => $value) {
      if (!isset($this->validate[$column])) {
        continue;
      }
      foreach ($this->validate[$column] as $conditionKey => $conditionValue) {
        $validate = $this->validateValue($value, $conditionKey, $conditionValue);
        if (!$validate) {
          $this->errors[$column] = $conditionKey;
          break;
        }
      }
    }
    if (count($this->errors) < 1) {
      return true;
    }
    return false;
  }

  public function getErrors() {
    return $this->errors;
  }

  public function isNew() {
    return $this->isNew;
  }

  public function isSaved() {
    return $this->isSaved;
  }

  public function save($options = array()) {
    if ($this->isDeleted) {
      return false;
    }
    $defaultOptions = array('validate' => true);
    $options = array_merge($defaultOptions, $options);
    if ($options['validate'] AND !$this->isValid()) {
      return false;
    }
    if ($this->isSaved) {
      return true;
    }
    $db = self::connection();
    if ($this->isNew) {
      $query = $db->insertQuery($this->table);
      $this->data[$this->primaryKey] = $query->addPairs($this->data)->execute();
    }
    else {
      $query = $db->updateQuery($this->table);
      foreach ($this->data as $column => $value) {
        $query->set($column, $value);
      }
      $query->where($this->primaryKey . ' = ?');
      $query->addVar($this->data[$this->primaryKey]);
      $query->execute();
    }
    $this->isNew = FALSE;
    $this->isSaved = TRUE;
    return true;
  }

  public function delete() {
    $db = self::connection();
    $db->deleteQuery($this->table)
      ->where($this->primaryKey . ' = ?')
      ->addVar($this->data[$this->primaryKey])
      ->execute();
  }

  public static function all(SelectQuery $selector = NULL) {
    $db = self::connection();
    $class = get_called_class();
    if (!isset($selector)) {
      $selector = SelectQuery::create();
    }
    $selector->from(self::$models[$class]['table']);
    $result = $db->executeSelect($selector);
    $allArray = array();
    while ($assoc = $result->fetchAssoc()) {
      $allArray[] = self::createFromAssoc($class, $assoc);
    }
    return $allArray;
  }

  public function addToCache() {
    $class = get_class($this);
    if (!is_array(self::$cache[$class])) {
      self::$cache[$class] = array();
    }
    self::$cache[$class][$this->data[$this->primaryKey]] = $this;
  }

  public static function find($primaryKey) {
    $db = self::connection();
    $class = get_called_class();
    if (isset(self::$cache[$class][$primaryKey])) {
      return self::$cache[$class][$primaryKey];
    }
    $query = $db->selectQuery(self::$models[$class]['table']);
    $query->where(self::$models[$class]['primaryKey'] . ' = ?');
    $query->addVar($primaryKey);
    $query->limit(1);
    $result = $db->execute($query);
    if (!$result->hasRows()) {
      return FALSE;
    }
    return self::createFromAssoc($class, $result->fetchAssoc());
  }

  public static function exists($primaryKey) {
    $db = self::connection();
    $class = get_called_class();
    $query = $db->selectQuery();
    $query->count();
    $query->where(self::$models[$class]['primaryKey'] . ' = ?');
    $query->addVar($primaryKey);
    return $db->count(self::$models[$class]['table'], $query) > 0;
  }

  public static function first(SelectQuery $selector = NULL) {
    $db = self::connection();
    $class = get_called_class();
    if (!isset($selector)) {
      $selector = SelectQuery::create();
    }
    $selector->from(self::$models[$class]['table']);
    $selector->limit(1);
    $result = $db->executeSelect($selector);
    if (!$result->hasRows()) {
      return FALSE;
    }
    return self::createFromAssoc($class, $result->fetchAssoc());
  }

  public static function last(SelectQuery $selector = NULL) {
    $db = self::connection();
    $class = get_called_class();
    if (!isset($selector)) {
      $selector = SelectQuery::create();
    }
    $selector->from(self::$models[$class]['table']);
    $selector->reverseOrder()->limit(1);
    $result = $db->executeSelect($selector);
    if (!$result->hasRows()) {
      return FALSE;
    }
    return self::createFromAssoc($class, $result->fetchAssoc());
  }

  public static function count(SelectQuery $selector = NULL) {
    $db = self::connection();
    $class = get_called_class();
    return $db->count(self::$models[$class]['table'], $selector);
  }

  public function json() {
    if (extension_loaded('json')) {
      return json_encode($this->data);
    }
    else {
      return tr('Unsupported');
    }
  }
}

class InvalidModelException extends Exception { }
class DatabaseNotConnectedException extends Exception { }
class TableNotFoundException extends Exception { }

class RecordPropertyNotFoundException extends Exception { }
class RecordMethodNotFoundException extends Exception { }