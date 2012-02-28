<?php
/*
 * Class for working with flat file databases
 *
 * @package PeanutCMS
 */

/**
 * Flatfiles class
 */
class Flatfiles {

  /**
   * An index of tables and their indexes
   * @var array
   */
  var $tableIndex;
  
  /**
   * An index of relations between table rows
   * @var array
   */
  var $relations;

  /**
   * Constructor
   */
  function Flatfiles() {
    return $this->__construct();
  }

  /**
   * PHP5-style constructor
   */
  function __construct() {
    global $PEANUT;
    // Create index of tables and their indexes
    $this->tableIndex = array();
    $dataDir = opendir(PATH . DATA);
    if (!$dataDir) {
      $PEANUT['errors']->notification('warning', tr("Can't access the directory %1", PATH . DATA), true, 'data-accessible');
    }
    else {
      while (($tableName = readdir($dataDir)) !== false) {
        if (!is_dir(PATH . DATA . $tableName) OR $tableName == '.' OR $tableName == '..')
          continue;
        $this->tableIndex[$tableName] = array('rows' => 0, 'indexes' => array());
        $tableDir = opendir(PATH . DATA . $tableName);
        if (!$tableDir) {
          $PEANUT['errors']->notification('warning', tr("Can't access the directory %1", PATH . DATA . $tableName), true, $tableName . '-accesible');
          continue;
        }
        while (($fileName = readdir($tableDir)) !== false) {
          $fileNameComponents = explode('.', $fileName);
          if (!isset($fileNameComponents[2]) OR $fileNameComponents[2] != 'php')
            continue;
          if ($fileNameComponents[1] == 'row')
            $this->tableIndex[$tableName]['rows']++;
          else if ($fileNameComponents[1] == 'index')
            $this->tableIndex[$tableName]['indexes'][$fileNameComponents[0]] = false;
        }
      }
    }
    if (!is_writable(PATH . DATA . 'autoincrement.index.php'))
      $PEANUT['errors']->notification('warning', tr("Can't access the file %1", PATH . DATA . 'autoincrement.index.php'), true, 'autoincrement-accessible');
    $this->getIncrementationIndex();
    $this->updateIncrementationIndex();
  }

  /**
   * PHP5-style destructor
   *
   * @return bool true
   */
  function __destruct() {
    return true;
  }

  /**
   * Return multiple (default all) rows from a table
   *
   * @param string $table Table name (directory)
   * @param array $ids Ids of rows to get, default is all
   * @param array $columns Names of columns to include in result, default is all
   * @return array Data array of the format array(ROWID => array(COLUMN => VALUE, ...), ...) or
   * if only one column is requested array(ROWID => VALUE, ...)
   */
  function getRows($table, $ids = null, $columns = null) {
    if (!isset($ids) OR !is_array($ids)) {
      $ids = array();
      $dir = opendir(PATH . DATA . $table);
      if (!$dir)
        return false;
      while (($fileName = readdir($dir)) !== false) {
        $fileNameComponents = explode('.', $fileName);
        if ($fileNameComponents[1] == 'row' AND $fileNameComponents[2] == 'php')
          $ids[] = $fileNameComponents[0];
      }
    }
    $data = array();
    foreach ($ids as $id) {
      $row = $this->getRow($table, $id);
      if (!$row)
        continue;
      if (!isset($columns) OR !is_array($columns)) {
        $data[$id] = $row;
      }
      else if (count($columns) == 1) {
        $data[$id] = $row[$columns[0]];
      }
      else {
        $data[$id] = array();
        $columnsFlipped = array_flip($columns);
        foreach ($row as $key => $value) {
          if (isset($columnsFlipped[$key]))
            $data[$id][$key] = $value;
        }
      }
    }
    return $data;
  }

  /**
   * Check if a row exists in a table
   *
   * @param string $table Table name (directory)
   * @param string $id Row ID (file name)
   * @return bool True if row exists, false otherwise
   */
  function rowExists($table, $id) {
    return file_exists(PATH . DATA . $table . '/' . $id . '.row.php');
  }

  /**
   * Return a row (file) from a database table
   *
   * @param string $table Table name (directory)
   * @param string $id Row ID (file name)
   * @return array Associative array containing row-data
   */
  function getRow($table, $id) {
    global $PEANUT;
    if (!file_exists(PATH . DATA . $table . '/' . $id . '.row.php'))
      return false;
    $fileContent = file_get_contents(PATH . DATA . $table . '/' . $id . '.row.php');
    if (!$fileContent)
      return false;
    $file = explode('?>', $fileContent);
    $dataArray = $PEANUT['configuration']->parseData($file[1]);
    $dataArray['id'] = $id;
    return $dataArray;
  }

  function insertRow($table, $id, $fields) {
    global $PEANUT;
    if (!is_writable(PATH . DATA . $table . '/'))
      return false;
    $file = fopen(PATH . DATA . $table . '/' . $id . '.row.php', 'w');
    if (!$file)
      return false;
    if (isset($fields['id']))
      unset($fields['id']);
    $data = $PEANUT['configuration']->compileData($fields);
    fwrite($file, "<?php exit(); ?>\n" . $data);
    fclose($file);
    $this->updateIndexes($table, $id, $fields);
    return true;
  }
  
  function updateRow($table, $id, $fields) {
    global $PEANUT;
    if (!$this->rowExists($table, $id))
      return false;
    $row = $this->getRow($table, $id);
    $row = array_merge($row, $fields);
    return $this->insertRow($table, $id, $row);
  }

  function removeRow($table, $id) {
    global $PEANUT;
    if (!is_writable(PATH . DATA . $table . '/'))
      return false;
    if (!unlink(PATH . DATA . $table . '/' . $id . '.row.php'))
      return false;
    $this->updateIndexes($table, $id, null);
    return true;
  }

  /**
   * Get the incrementation index
   */
  function getIncrementationIndex() {
    global $PEANUT;
    $fileContent = file_get_contents(PATH . DATA . 'autoincrement.index.php');
    if (!$fileContent)
      return false;
    $file = explode('?>', $fileContent);
    $dataArray = $PEANUT['configuration']->parseData($file[1]);
    foreach ($dataArray as $table => $id) {
      $this->tableIndex[$table]['incrementation'] = (int)$id;
    }
  }
  
  function updateIncrementationIndex() {
    global $PEANUT;
    if (!is_writable(PATH . DATA))
      return false;
    $file = fopen(PATH . DATA . 'autoincrement.index.php', 'w');
    if (!$file)
      return false;
    $dataArray = array();
    foreach ($this->tableIndex as $table => $data) {
      if (isset($data['incrementation']) AND is_int($data['incrementation']))
        $dataArray[$table] = $data['incrementation'];
      else
        $dataArray[$table] = 0;
    }
    $data = $PEANUT['configuration']->compileData($dataArray);
    fwrite($file, "<?php exit(); ?>\n" . $data);
    fclose($file);
    return true;
  }
  
  /**
   * Auto-increment an id
   * @param string $table Table name
   * @param int Return an id that does not already exist in the table
   */
  function incrementId($table) {
    $this->tableIndex[$table]['incrementation']++;
    while (file_exists(PATH . DATA . $table . '/' . $this->tableIndex[$table]['incrementation'] . '.row.php'))
     $this->tableIndex[$table]['incrementation']++;
    if (!$this->updateIncrementationIndex())
      return false;
    return $this->tableIndex[$table]['incrementation'];
  }
  
  /**
   * Get an index for a table column if it exists
   *
   * @param string $table Table name
   * @param string $column Column name
   * @return array Index-array of the format array(ROWID => FIELDVALUE, ...)
   */
  function getIndex($table, $column) {
    global $PEANUT;
    if (!isset($this->tableIndex[$table]['indexes'][$column]) OR $this->tableIndex[$table]['indexes'][$column] == false) {
      $fileContent = file_get_contents(PATH . DATA . $table . '/' . $column . '.index.php');
      if (!$fileContent)
        return false;
      $file = explode('?>', $fileContent);
      $this->tableIndex[$table]['indexes'][$column] = $PEANUT['configuration']->parseData($file[1]);
    }
    return $this->tableIndex[$table]['indexes'][$column];
  }
  
  function getRelIndex($table1, $table2) {
    global $PEANUT;
    if (!isset($this->relations[$table1 . '.' . $table2]) OR $this->relations[$table1 . '.' . $table2] == false) {
      $fileContent = file_get_contents(PATH . DATA . $table1 . '.' . $table2 . '.rel.php');
      if (!$fileContent)
        return false;
      $file = explode('?>', $fileContent);
      $this->relations[$table1 . '.' . $table2] = $PEANUT['configuration']->parseData($file[1], false, false);
    }
    return $this->relations[$table1 . '.' . $table2];
  }
  
  function relationExists($table1, $table2, $row1, $row2) {
    global $PEANUT;
    if (!$this->relIndexExists($table1, $table2))
      return false;
    if (!isset($this->relations[$table1 . '.' . $table2]) OR $this->relations[$table1 . '.' . $table2] == false)
      $this->getRelIndex($table1, $table2);
    $search = array_keys($this->relations[$table1 . '.' . $table2][0], $row1);
    foreach ($search as $i) {
      if ($this->relations[$table1 . '.' . $table2][1][$i] == $row2)
        return $i;
    }
    return false;
  }
  
  function getRelations($table1, $table2, $row1 = null, $row2 = null) {
    global $PEANUT;
    if (!$this->relIndexExists($table1, $table2))
      return false;
    if (!isset($this->relations[$table1 . '.' . $table2]) OR $this->relations[$table1 . '.' . $table2] == false)
      $this->getRelIndex($table1, $table2);
    $relations = array();
    if (isset($row1)) {
      $search = array_keys($this->relations[$table1 . '.' . $table2][0], $row1);
      foreach ($search as $i)
        $relations[] = $this->relations[$table1 . '.' . $table2][1][$i];
    }
    else if (isset($row2)) {
      $search = array_keys($this->relations[$table1 . '.' . $table2][1], $row2);
      foreach ($search as $i)
        $relations[] = $this->relations[$table1 . '.' . $table2][0][$i];
    }
    return $relations;
  }
  
  function addRelation($table1, $table2, $row1, $row2) {
    global $PEANUT;
    if (!$this->relIndexExists($table1, $table2))
      return false;
    if (!isset($this->relations[$table1 . '.' . $table2]) OR $this->relations[$table1 . '.' . $table2] == false)
      $this->getRelIndex($table1, $table2);
    if (($i = $this->relationExists($table1, $table2, $row1, $row2)) !== false)
      return false;
    $this->relations[$table1 . '.' . $table2][0][] = $row1;
    $this->relations[$table1 . '.' . $table2][1][] = $row2;
    $this->createRelIndex($table1, $table2);
  }
  
  function removeRelation($table1, $table2, $row1, $row2) {
    global $PEANUT;
    if (!$this->relIndexExists($table1, $table2))
      return false;
    if (($i = $this->relationExists($table1, $table2, $row1, $row2)) === false)
      return false;
    unset($this->relations[$table1 . '.' . $table2][0][$i]);
    unset($this->relations[$table1 . '.' . $table2][1][$i]);
    return $this->createRelIndex($table1, $table2);
  }

  /**
   * Build or rebuild an index
   *
   * @param string $table Table name
   * @param string $column Column to build index from
   * @param string $dataArray Optional dataArray, default is to get all rows
   * @return bool True if successful, false if not
   */
  function buildIndex($table, $column, $dataArray = null) {
    global $PEANUT;
    if (!is_writable(PATH . DATA . $table . '/'))
      return false;
    $file = fopen(PATH . DATA . $table . '/' . $column . '.index.php', 'w');
    if (!$file)
      return false;
    if (!isset($dataArray))
      $dataArray = $this->getRows($table, null, array($column));
    $this->tableIndex[$table]['indexes'][$column] = $dataArray;
    $data = $PEANUT['configuration']->compileData($dataArray);
    fwrite($file, "<?php exit(); ?>\n" . $data);
    fclose($file);
    return true;
  }
  
  /**
   * Create or re-create relations index
   *
   * @param string $table1 Name of first table
   * @param string $table2 Name of seconds table
   * @return bool
   */
  function createRelIndex($table1, $table2) {
    global $PEANUT;
    if (!is_writable(PATH . DATA . '/'))
      return false;
    $file = fopen(PATH . DATA . $table1 . '.' . $table2 . '.rel.php', 'w');
    if (!$file)
      return false;
    if (isset($this->relations[$table1 . '.' . $table2]))
      $dataArray = $this->relations[$table1 . '.' . $table2];
    else
      $dataArray = array();
    $data = $PEANUT['configuration']->compileData($dataArray, false, false);
    fwrite($file, "<?php exit(); ?>\n" . $data);
    fclose($file);
    return true;
  }

  /**
   * Update indexes of a table
   *
   * @param string $table Table name
   * @param string $id Row ID
   * @param row $row Updated or new row data
   * @return bool True if successful, false if not
   */
  function updateIndexes($table, $id, $row = null) {
    if (!isset($this->tableIndex[$table]['indexes']) OR !is_array($this->tableIndex[$table]['indexes']))
      return false;
    if (count($this->tableIndex[$table]['indexes']) < 1)
      return true;
    foreach ($this->tableIndex[$table]['indexes'] as $column => $dataArray) {
      if ($dataArray == false)
        $dataArray = $this->getIndex($table, $column);
      if (!isset($row))
        unset($dataArray[$id]);
      else
        $dataArray[$id] = $row[$column];
      if (!$this->buildIndex($table, $column, $dataArray))
        return false;
    }
    return true;
  }

  /**
   * Return the rowid to an index-value
   *
   * @param <type> $table
   * @param <type> $column
   * @param <type> $value
   * @return <type>
   */
  function indexFind($table, $column, $value) {
    if (!isset($this->tableIndex[$table]['indexes'][$column]))
      return false;
    if ($this->tableIndex[$table]['indexes'][$column] == false)
      $this->tableIndex[$table]['indexes'][$column] = $this->getIndex($table, $column);
    return array_search($value, $this->tableIndex[$table]['indexes'][$column]);
  }

  /**
   * Check if an index exists on a column
   *
   * @param string $table Table name
   * @param string $column Indexed column
   */
  function indexExists($table, $column) {
    return file_exists(PATH . DATA . $table . '/' . $column . '.index.php');
  }
  
  /**
   * Check if a relation index exists bewteen two tables
   * 
   * @param string $table1 Name of first table
   * @param string $table2 Name of seconds table
   * @return bool
   */
  function relIndexExists($table1, $table2) {
    return file_exists(PATH . DATA . $table1 . '.' . $table2 . '.rel.php');
  }
  
  public function tableExists($table) {
    return file_exists(PATH . DATA . $table);
  }
  
  public function createTable($table) {
    return mkdir(PATH . DATA . $table);
  }
  
}