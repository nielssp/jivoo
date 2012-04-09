<?php

class Configuration implements IModule {

  private $errors = NULL;

  public function getErrors() {
    return $this->errors;
  }

  private $data = array();

  private $file;

  public function __construct(Errors $errors, $cfgFile = NULL) {
    $this->errors = $errors;

    if (!isset($cfgFile)) {
      $cfgFile = p(CFG . 'config.cfg.php');
    }
    $this->file = $cfgFile;
    if (!is_readable($this->file)) {
      // Attempt to create configuration-file
      $file = fopen($this->file, 'w');
      if (!$file) {
        $this->errors->fatal(tr('Fatal error'), tr('%1 is missing or inaccessible and could not be created', $this->file));
      }
      fwrite($file, "<?php exit; ?>\n");
      fclose($file);
    }
    if (!is_writable($this->file)) {
      $this->errors->notification('warning', tr('%1 is not writable', $this->file), true, 'settings-writable');
    }
    $fileContent = file_get_contents($this->file);
    if ($fileContent === FALSE) {
      $this->errors->fatal(tr('Fatal error'), tr('%1 is missing or inaccessible', $this->file));
    }
    $file = explode('?>', $fileContent);
    $this->data = $this->parseData($file[1], true);
  }

  public static function getDependencies() {
    return array('errors');
  }

  /**
   * Update a configuration key
   *
   * @param string $key The configuration key to access
   * @param mixed $value The variable to associate with the key. Could be a string/array/object etc.
   * @return bool True if successful, false if not
   */
  public function set($key, $value) {
    if (isset($key) AND isset($value) AND $key != '')
      $this->data[$key] = $value;
    else
      unset($this->data[$key]);

    if (!is_writable($this->file))
      return false;
    $filePointer = fopen($this->file, 'w');
    if (!$filePointer)
      return false;
    $data = Configuration::compileData($this->data, true);
    fwrite($filePointer, "<?php exit(); ?>\n" . $data);
    fclose($filePointer);
    return true;
  }

  /**
   * Delete a configuration key
   *
   * Function is an alias for update($key, NULL)
   *
   * @uses update()
   * @param string $key The configuration key to delete
   * @return bool True if successful, false if not
   */
  public function delete($key) {
    return $this->set($key, NULL);
  }

  /**
   * Return the value of a configuration key
   *
   * @param string $key Configuration key
   * @return mixed The content of the configuration key or false if key
   * doesn't exist
   */
  public function get($key) {
    if ($key[($keylen = strlen($key)) - 1] == '.') {
      $result = array();
      foreach ($this->data as $dkey => $dval) {
        if (substr_compare($key, $dkey, 0, $keylen) == 0) {
          $result[substr($dkey, $keylen)] = $dval;
        }
      }
      return $result;
    }
    if (!isset($this->data[$key]))
      return FALSE;
    return $this->data[$key];
  }

  /**
   * Check if a key exists
   *
   * @param string $key Configuration key
   * @return bool True if it exists false if not
   */
  public function exists($key) {
    return isset($this->data[$key]);
  }

  /**
   * Parse data string into data array
   *
   * @param string $data Data string
   * @param bool $serialize Wether or not to unserialize data that appears to be serialized
   * @param bool $associative Return an associative array of keys and value
   * @return array Data fields in an array
   */
  public static function parseData($data, $serialize = false, $associative = true) {
    $fields = explode('|', $data);
    $dataArray = array();
    $dataKey = '';
    foreach ($fields as $key => $value) {
      if ($key & 1) {
        if ($associative) {
          $dataArray[$dataKey] = html_entity_decode($value, ENT_NOQUOTES, 'UTF-8');
          if ($serialize AND isSerialized($dataArray[$dataKey]))
            $dataArray[$dataKey] = unserialize($dataArray[$dataKey]);
        }
        else {
          $dataArray[0][] = $dataKey;
          $dataArray[1][] = html_entity_decode($value, ENT_NOQUOTES, 'UTF-8');
        }
      }
      else {
        $dataKey = trim($value);
      }
    }
    return $dataArray;
  }

  /**
   * Assemble data array into data string
   *
   * @param array $fields Data array
   * @param bool $serialize Wether or not to serialize arrays, if false then arrays and objects will not be included in data
   * @return string Data string
   */
  public static function compileData($fields, $serialize = false, $associative = true) {
    $data = '';
    if (!$associative) {
      foreach ($fields[0] as $i => $key) {
        $value = $fields[1][$i];
        $key = str_replace('|', '&#124;', htmlentities($key, ENT_NOQUOTES, 'UTF-8'));
        $value = str_replace('|', '&#124;', htmlentities($value, ENT_NOQUOTES, 'UTF-8'));
        $data .= $key . '|' . $value . "|\n";
      }
    }
    else {
      foreach ($fields as $key => $value) {
        $key = str_replace('|', '&#124;', htmlentities($key, ENT_NOQUOTES, 'UTF-8'));
        if (is_array($value) OR is_object($value)) {
          if ($serialize)
            $value = serialize($value);
          else
            $value = '';
        }
        $value = str_replace('|', '&#124;', htmlentities($value, ENT_NOQUOTES, 'UTF-8'));
        $data .= $key . '|' . $value . "|\n";
      }
    }
    return $data;
  }
}