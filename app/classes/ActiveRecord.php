<?php
abstract class ActiveRecord implements IModel {
  private static $models = array();
  public static function addModel($class, IDataSource $dataSource) {
    $schema = $dataSource->getSchema();
    self::$models[$class] = array(
      'source' => $dataSource,
      'table' => $dataSource->getName(),
      'schema' => $schema,
      'columns' => $schema->getColumns(),
      'primaryKey' => $schema->getPrimaryKey()
    );
  }
  public static function connect(IDataSource $dataSource) {
    $class = get_called_class();
    $schema = $dataSource->getSchema();
    self::$models[$class] = array(
      'source' => $dataSource,
      'table' => $dataSource->getName(),
      'schema' => $schema,
      'columns' => $schema->getColumns(),
      'primaryKey' => $schema->getPrimaryKey()
    );
  }
  private static $cache = array();

  private $table;
  private $schema;
  private $dataSource;
  private $primaryKey;
  private $data;
  private $changed;

  private $isNew = FALSE;
  private $isSaved = TRUE;
  private $isDeleted = FALSE;

  protected $validate = array();
  protected static $validateOverride = array();

  protected $defaults = array();
  protected static $defaultsOverride = array();

  protected $virtuals = array();
  protected static $virtualsOverride = array();
  
  protected $fields = array();
  protected static $fieldsOverride= array();

  protected $hasOne = array();
  protected $hasMany = array();
  protected $belongsTo = array();
  protected $hasAndBelongsToMany = array();

  private $associations = array();

  private $errors = array();

  public function __set($property, $value) {
    if (isset($this->virtuals[$property]) AND isset($this->virtuals[$property]['set'])) {
      call_user_func(array($this, $this->virtuals[$property]['set']), $value);
      $this->isSaved = FALSE;
    }
    else if (array_key_exists($property, $this->data) AND $property != $this->primaryKey) {
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
    if (isset($this->virtuals[$property]) AND isset($this->virtuals[$property]['get'])) {
      return call_user_func(array($this, $this->virtuals[$property]['get']));
    }
    else if (array_key_exists($property, $this->data)) {
      return $this->data[$property];
    }
    else {
      throw new RecordPropertyNotFoundException(
        tr('Property "%1" was not found in model "%2".', $property, get_class($this))
      );
    }
  }
  
  public function __isset($property) {
    return isset($this->data[$property])
      OR isset($this->virtuals[$property]);
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

      if (!isset($arguments[0])) {
        $arguments[0] = NULL;
      }

      if ($association == 'hasMany' OR $association == 'hasAndBelongsToMany') {
        if ($this->isNew) {
          return FALSE;
        }
        switch ($procedure) {
          case 'get':
            return $this->manyGet($options, $arguments[0]);
          case 'count':
            return $this->manyCount($options, $arguments[0]);
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

    $select->where($options['thisKey'] . ' = ?');
    $select->addVar($this->data[$this->primaryKey]);

    $result = self::$models[$otherClass]['source']->select($select);

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

  private function manyCount($options, SelectQuery $customSelect = NULL) {
    $thisClass = get_class($this);
    $otherClass = $options['class'];

    $otherPrimaryKey = self::$models[$otherClass]['primaryKey'];

    if (!isset($customSelect)) {
      $select = SelectQuery::create();
    }
    else {
      $select = $customSelect;
    }
    $select->count();
    if (isset($options['join'])) {
      $select->join($options['join'], $otherPrimaryKey, $options['otherKey']);
    }

    $select->where($options['thisKey'] . ' = ?');
    $select->addVar($this->data[$this->primaryKey]);

    $result = self::$models[$otherClass]['source']->count($select);

    if (isset($options['count']) AND !isset($customSelect)) {
      if ($this->data[$options['count']] != $result) {
        $this->data[$options['count']] = $result;
        $this->isSaved = FALSE;
        $this->save();
      }
    }
    return $result;
  }

  private function manyHas($options, ActiveRecord $record) {
    $thisClass = get_class($this);
    $otherClass = $options['class'];

    $otherPrimaryKey = self::$models[$otherClass]['primaryKey'];

    $query = SelectQuery::create();
    if (isset($options['join'])) {
      if ($this->dataSource instanceof ITable) {
        $query->where($options['thisKey'] . ' = ? AND ' . $options['otherKey'] . ' = ?');
        $query->addVar($this->data[$this->primaryKey]);
        $query->addVar($record->data[$record->primaryKey]);
        /** @todo Maybe find some better way of doing this.... */
        return $this->dataSource->getOwner()->getTable($options['join'])->count($query) > 0;
      }
    }
    else {
      $query->where($options['thisKey'] . ' = ?');
      $query->addVar($this->data[$this->primaryKey]);
      return $this->dataSource->count($query) > 0;
    }
  }

  private function manyAdd($options, ActiveRecord $record) {
    $thisClass = get_class($this);
    $otherClass = $options['class'];

    if ($this->manyHas($options, $record)) {
      return FALSE;
    }

    if (isset($options['join'])) {
      if ($record->isNew) {
        return FALSE;
      }
      if ($this->dataSource instanceof ITable) {
        $query = $this->dataSource->getOwner()->getTable($options['join'])->insert();
        $query->addPair($options['thisKey'], $this->data[$this->primaryKey]);
        $query->addPair($options['otherKey'], $record->data[$record->primaryKey]);
        $query->execute();
        return TRUE;
      }
      return FALSE;
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
    $thisClass = get_class($this);
    $otherClass = $options['class'];

    if (isset($options['join'])) {
      if ($record->isNew) {
        return FALSE;
      }
      if ($this->dataSource instanceof ITable) {
        $query = $this->dataSource->getOwner()->getTable($options['join'])->delete();
        $query->where($options['thisKey'] . ' = ? AND ' . $options['otherKey'] . ' = ?');
        $query->addVar($this->data[$this->primaryKey]);
        $query->addVar($record->data[$record->primaryKey]);
        $query->execute();
        return TRUE;
      }
      return FALSE;
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
      $query = self::connection($otherClass)->select();
      $query->where(self::$models[$otherClass]['primaryKey'] . ' = ?');
    }
    else {
      $primaryKey = $this->data[$this->primaryKey];
      $query = self::connection($otherClass)->select();
      $query->where($options['thisKey'] . ' = ?');
    }
    $query->addVar($primaryKey);
    $query->limit(1);
    $result = $query->execute();
    if (!$result->hasRows()) {
      return FALSE;
    }
    return self::createFromAssoc($otherClass, $result->fetchAssoc());
  }

  private function oneSet($options, ActiveRecord $record) {
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

  private function __construct($data = array()) {
    $class = get_class($this);
    if (!isset(self::$models[$class])) {
      throw new InvalidModelException(tr('The model "%1" has not been added to ActiveRecord.', $class));
    }
    $this->dataSource = self::$models[$class]['source'];
    $this->table = self::$models[$class]['table'];
    $this->primaryKey = self::$models[$class]['primaryKey'];
    $this->schema = self::$models[$class]['schema'];
    $this->data = array();
    
    if (isset(self::$validateOverride[$class])) {
      $this->validate = self::$validateOverride[$class];
    }
    if (isset(self::$defaultsOverride[$class])) {
      $this->defaults = self::$defaultsOverride[$class];
    }
    if (isset(self::$virtualsOverride[$class])) {
      $this->virtuals = self::$virtualsOverride[$class];
    }
    if (isset(self::$fieldsOverride[$class])) {
      $this->fields = self::$fieldsOverride[$class];
    }
    
    foreach (self::$models[$class]['columns'] as $column) {
      if (isset($data[$column])) {
        $this->data[$column] = $data[$column];
      }
      else {
        $this->data[$column] = NULL;
      }
      if ($column == $this->primaryKey) {
        continue;
      }
      if (!isset($this->validate[$column])) {
        $this->validate[$column] = array();
      }
      if (isset($this->schema->$column)) {
        $info = $this->schema->$column;
        if ($info['type'] == 'integer') {
          /** @todo Handle signed integers */
          $this->validate[$column]['isInteger'] = TRUE;
          if (isset($this->validate[$column]['maxValue'])) {
            $this->validate[$column]['maxValue'] = min($this->validate[$column]['maxValue'], 4294967295);
          }
          else {
            $this->validate[$column]['maxValue'] = 4294967295;
          }
          if (isset($this->validate[$column]['minValue'])) {
            $this->validate[$column]['minValue'] = max(0, $this->validate[$column]['minValue']);
          }
          else {
            $this->validate[$column]['minValue'] = 0;
          }
        }
        else if ($info['type'] == 'float') {
          $this->validate[$column]['isFloat'] = TRUE;
        }
        else if ($info['type'] == 'boolean') {
          $this->validate[$column]['isBoolean'] = TRUE;
        }
        if (isset($info['length']) AND $info['type'] != 'float' AND $info['type'] != 'integer' AND $info['type'] != 'boolean') {
          if (isset($this->validate[$column]['maxLength'])) {
            $this->validate[$column]['maxLength'] = min($this->validate[$column]['maxLength'], $info['length']);
          }
          else {
            $this->validate[$column]['maxLength'] = $info['length'];
          }
        }
        if (isset($info['key']) AND ($info['key'] == 'primary' OR $info['key'] == 'unique')) {
          $this->validate[$column]['unique'] = TRUE;
        }
        if (isset($info['null']) AND $info['null'] == FALSE) {
          $this->validate[$column]['null'] = FALSE;
        }
        if (isset($info['default'])) {
          if (!isset($this->defaults[$column])) {
            $this->defaults[$column] = $info['default'];
          }
        }
      }
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
      $this->associations['count' . $options['plural']] = array('hasMany', 'count', $class);
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
      $this->associations['count' . $options['plural']] = array('hasAndBelongsToMany', 'count', $class);
      $this->associations['has' . $class] = array('hasAndBelongsToMany', 'has', $class);
      $this->associations['add' . $class] = array('hasAndBelongsToMany', 'add', $class);
      $this->associations['remove' . $class] = array('hasAndBelongsToMany', 'remove', $class);
    }
  }

  protected static function connection($class) {
    if (!isset(self::$models[$class])) {
      throw new DatabaseNotConnectedException('This ActiveRecord is not connected to a database.');
    }
    return self::$models[$class]['source'];
  }

  private static function createFromAssoc($class, $assoc) {
    if (!class_exists($class)) {
      throw new Exception(tr('%1 is not a class', $class));
    }
    $new = new $class($assoc);
    /*
    foreach ($assoc as $property => $value) {
      if (in_array($property, self::$models[$class]['columns'])) {
        $new->data[$property] = $value;
      }
    }
     */
    $new->addToCache();
    return $new;
  }

  public static function create($data = array()) {
    $class = get_called_class();
    $new = new $class();
    $new->isNew = TRUE;
    $new->isSaved = FALSE;
    $keys = array_unique(array_merge(array_keys($new->defaults), array_keys($data)));
    foreach ($keys as $key) {
      try {
        if (isset($data[$key]) AND isset($new->fields[$key])) {
          $new->$key = $data[$key];
        }
        else if (isset($new->defaults[$key])) {
          $value = $new->defaults[$key];
          if (is_array($value)) {
            if (is_callable($value[0])) {
              $function = array_shift($value);
              $new->$key = call_user_func_array($function, $value);
            }
          }
          else {
            $new->$key = $value;
          }
        }
      }
      catch (RecordPropertyNotFoundException $ex) {
        // ignore
      }
    }
    return $new;
  }
  
  public function edit($data) {
    if (!is_array($data)) {
      return;
    }
    foreach ($data as $key => $value) {
      if (isset($this->fields[$key])) {
        $this->$key = $value;
      }
    }
  }

  protected function validateValue($column, $value, $conditionKey, $conditionValue) {
    $validate = array();
    $class = get_class($this);
    if (is_int($conditionKey) AND is_array($conditionValue)) {
      foreach ($conditionValue as $subConditionKey => $subConditionValue) {
        $validate = $this->validateValue($column, $value, $subConditionKey, $subConditionValue);
        if (!$validate) {
          return FALSE;
        }
      }
      return TRUE;
    }
    if ($conditionKey != 'presence'
        AND $conditionKey != 'null' AND empty($value) AND !is_numeric($value)) {
      return TRUE;
    }
    switch ($conditionKey) {
      case 'presence':
        return (!empty($value) OR is_numeric($value)) == $conditionValue;
      case 'null':
        return is_null($value) == $conditionValue;
      case 'email':
        return preg_match("/^[a-z0-9.!#$%&*+\/=?^_`{|}~-]+@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i", $value) == 1;
      case 'minLength':
        return strlen($value) >= $conditionValue;
      case 'maxLength':
        return strlen($value) <= $conditionValue;
      case 'isNumeric':
        return is_numeric($value) == $conditionValue;
      case 'isInteger':
        return (preg_match('/\A[+-]?\d+\Z/', $value) == 1) == $conditionValue;
      case 'isFloat':
        return (preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/', $value) == 1) == $conditionValue;
      case 'isBoolean':
        return ($value == 1 OR $value == 0) == $conditionValue;
      case 'minValue':
        $value = is_float($conditionValue) ? (float) $value : (int) $value;
        return $value >= $conditionValue;
      case 'maxValue':
        $value = is_float($conditionValue) ? (float) $value : (int) $value;
        return $value <= $conditionValue;
      case 'match':
        return preg_match($conditionValue, $value) == 1;
      case 'unique':
        $query = $this->dataSource->select()
          ->limit(1);
        if (!$this->isNew()) {
          $query->where($this->primaryKey . ' != ? AND ' . $column . ' = ?')
            ->addVar($this->data[$this->primaryKey]);
        }
        else {
          $query->where($column . ' = ?');
        }
        $result = $query->addVar($value)->execute();
        return $result->hasRows() != $conditionValue;
      case 'callback':
        return !is_callable($conditionValue) OR call_user_func($conditionValue, $value);
    }
    return TRUE;
  }

  protected function getMessage($rule, $value) {
    if (is_int($rule) AND is_array($value)) {
      if (isset($value['message'])) {
        return tr($value['message']);
      }
      return tr('Invalid value.');
    }
    switch ($rule) {
      case 'presence':
        return $value ? tr('Must not be empty.') : tr('Must be empty.');
      case 'null':
        return $value ? tr('Must be null.') : tr('Must not be null.');
      case 'email':
        return tr('Not a valid email address.');
      case 'minLength':
        return trn('Minimum length of %1 character.', 'Minimum length of %1 characters.', $value);
      case 'maxLength':
        return trn('Maximum length of %1 character.', 'Maximum length of %1 characters.', $value);
      case 'isNumeric':
        return $value ? tr('Must be numeric.') : tr('Must not be numeric.');
      case 'isInteger':
        return $value ? tr('Must be an integer.') : tr('Must not be an integer.');
      case 'isFloat':
        return $value ? tr('Must be a decimal number.') : tr('Must not be a decimal number.');
      case 'isBoolean':
        return $value ? tr('Must be boolean (1 or 0).') : tr('Must not be boolean.');
      case 'minValue':
        return tr('Minimum value of %1.', $value);
      case 'maxValue':
        return tr('Maximum value of %1.', $value);
      case 'unique':
        return $value ? tr('Must be unique.') : tr('Must not be unique.');
      case 'match':
      case 'callback':
      default:
        return tr('Invalid value.');
    }
  }

  public function isValid() {
    $this->errors = array();
    foreach ($this->data as $column => $value) {
      if (!is_scalar($value) AND !is_null($value)) {
        $this->errors[$column] = tr('Value not a scalar.');
      }
      if (!isset($this->validate[$column])) {
        continue;
      }
      foreach ($this->validate[$column] as $conditionKey => $conditionValue) {
        $validate = $this->validateValue($column, $value, $conditionKey, $conditionValue);
        if (!$validate) {
          $this->errors[$column] = $this->getMessage($conditionKey, $conditionValue);
          break;
        }
      }
    }
    if (count($this->errors) < 1) {
      return TRUE;
    }
    return FALSE;
  }


  public function getFields() {
    $fields = array_keys($this->fields);
    $virtualFields = array_keys($this->virtuals);
    return array_unique(array_merge($fields, $virtualFields));
  }

  public function getFieldType($field) {
    if (isset($this->schema->$field)) {
      $field = $this->schema->$field;
      return $field['type'];
    }
  }
  
  public function getFieldLabel($field) {
    if (!isset($this->fields[$field])) {
      return '';
    }
    return tr($this->fields[$field]);
  }
  
  public function isFieldRequired($field) {
    return isset($this->validate[$field])
      AND isset($this->validate[$field]['presence'])
      AND $this->validate[$field]['presence'];
  }
  
  public function isField($field) {
    return isset($this->field[$field]);
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
    foreach ($this->virtuals as $tasks) {
      if (isset($tasks['presave'])) {
        call_user_func(array($this, $tasks['presave']));
      }
    }
    if ($this->isNew) {
      $query = $this->dataSource->insert();
      $this->data[$this->primaryKey] = $query->addPairs($this->data)->execute();
    }
    else {
      $query = $this->dataSource->update();
      foreach ($this->data as $column => $value) {
        $query->set($column, $value);
      }
      $query->where($this->primaryKey . ' = ?');
      $query->addVar($this->data[$this->primaryKey]);
      $query->execute();
    }
    $this->isNew = FALSE;
    $this->isSaved = TRUE;
    foreach ($this->virtuals as $tasks) {
      if (isset($tasks['save'])) {
        call_user_func(array($this, $tasks['save']));
      }
    }
    return true;
  }

  public function delete() {
    $this->dataSource->delete()
      ->where($this->primaryKey . ' = ?')
      ->addVar($this->data[$this->primaryKey])
      ->execute();
  }

  public static function all(SelectQuery $selector = NULL) {
    $class = get_called_class();
    $dataSource = self::connection($class);
    if (!isset($selector)) {
      $selector = SelectQuery::create();
    }
    $result = $dataSource->select($selector);
    $allArray = array();
    while ($assoc = $result->fetchAssoc()) {
      $allArray[] = self::createFromAssoc($class, $assoc);
    }
    return $allArray;
  }

  public function addToCache() {
    $class = get_class($this);
    if (!isset(self::$cache[$class]) OR !is_array(self::$cache[$class])) {
      self::$cache[$class] = array();
    }
    self::$cache[$class][$this->data[$this->primaryKey]] = $this;
  }

  public static function find($primaryKey) {
    $class = get_called_class();
    $dataSource = self::connection($class);
    if (isset(self::$cache[$class][$primaryKey])) {
      return self::$cache[$class][$primaryKey];
    }
    $result = $dataSource->select()
      ->where(self::$models[$class]['primaryKey'] . ' = ?')
      ->addVar($primaryKey)
      ->limit(1)
      ->execute();
    if (!$result->hasRows()) {
      return FALSE;
    }
    $record = self::createFromAssoc($class, $result->fetchAssoc());
    $record->addToCache();
    return $record;
  }

  public static function exists($primaryKey) {
    $class = get_called_class();
    $dataSource = self::connection($class);
    $query = SelectQuery::create()
      ->where(self::$models[$class]['primaryKey'] . ' = ?')
      ->addVar($primaryKey);
    return $dataSource->count($query) > 0;
  }

  public static function first(SelectQuery $selector = NULL) {
    $class = get_called_class();
    $dataSource = self::connection($class);
    if (!isset($selector)) {
      $selector = SelectQuery::create();
    }
    $selector->limit(1);
    $result = $dataSource->select($selector);
    if (!$result->hasRows()) {
      return FALSE;
    }
    return self::createFromAssoc($class, $result->fetchAssoc());
  }

  public static function last(SelectQuery $selector = NULL) {
    $class = get_called_class();
    $dataSource = self::connection($class);
    if (!isset($selector)) {
      $selector = SelectQuery::create();
    }
    $selector->limit(1);
    $selector->reverseOrder()->limit(1);
    $result = $dataSource->select($selector);
    if (!$result->hasRows()) {
      return FALSE;
    }
    return self::createFromAssoc($class, $result->fetchAssoc());
  }

  public static function count(SelectQuery $selector = NULL) {
    $class = get_called_class();
    $dataSource = self::connection($class);
    return $dataSource->count($selector);
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
