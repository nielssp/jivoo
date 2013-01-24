<?php
/**
 * Represents a configuration file or subset
 *
 * Implements arrayaccess, so the []-operator can be used
 * to get and set configuration values.
 * @package ApakohPHP
 */
class AppConfig implements arrayaccess{
  
  private $data = array();
  
  private $file;
  
  private $updated = false;
  
  private $root = null;
  
  private $parent = null;
  
  public function __construct($configFile) {
    $this->root = $this;
    if (isset($configFile)) {
      if (file_exists($configFile)) {
        $this->data = include $configFile;
      }
      $this->file = $configFile;
    }
  }
  
  public function __destruct() {
    if (!isset($this->parent)) {
      $this->save();
    }
  }
  
  public function __get($property) {
    switch ($property) {
      case 'parent':
        return $this->$property;
    }
  }
  
  public function __set($property, $value) {
    switch ($property) {
      case 'defaults':
        $this->setDefaults(is_array($value) ? $value : array());
        break;
    }
  }
  
  public function getSubset($key) {
    $config = new AppConfig();
    if (!isset($this->data[$key])) {
      $this->data[$key] = array();
    }
    $config->data =& $this->data[$key];
    $config->parent = $this;
    $config->root = $this->root;
    return $config;
  }
  
  /**
   * Update a configuration key
   *
   * @param string $key The configuration key to access
   * @param mixed $value The variable to associate with the key. Could be a string/array/object etc.
   * @return bool True if successful, false if not
   */
  public function set($key, $value) {
    if (isset($key) AND isset($value) AND $key != '') {
      $this->data[$key] = $value;
    }
    else {
      $this->data[$key] = null;
    }
    $this->root->updated = true;
  }
  

  /**
   * Delete a configuration key
   *
   * Function is an alias for update($key, null)
   *
   * @uses update()
   * @param string $key The configuration key to delete
   * @return bool True if successful, false if not
   */
  public function delete($key) {
    unset($this->data[$key]);
  }
  
  /**
   * Set default values.
   * @param string|array Either a key as a string or an array of key/value pairs
   * @param mixed $value Value
   */
  public function setDefaults($key, $value = null) {
    if (is_array($key)) {
      $array = $key;
      foreach ($array as $key => $value) {
        if (!$this->exists($key)) {
          $this->set($key, $value);
        }
        else if (is_array($value)) {
          $this[$key]->setDefaults($value);
        }
      }
    }
    else {
      if (!$this->exists($key)) {
        $this->set($key, $value);
      }
    }
  }
  
  /**
   * Return the value of a configuration key
   *
   * @param string $key Configuration key
   * @param bool $arrayOnly Only return arrays
   * @return mixed The content of the configuration key or false if key
   * doesn't exist
   */
  public function get($key = '', $arrayOnly = false) {
    if (!isset($this->data[$key])) {
      return $arrayOnly ? array() : false;
    }
    if ($arrayOnly && !is_array($this->data[$key])) {
      return array();
    }
    return $this->data[$key];
  }
  
  public function getArray() {
    return $this->data;
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
   * Create valid PHP array representation
   * @param array $data Associative array
   * @param string $prefix Prefix to put in front of new lines
   * @return string PHP source
   */
  public static function phpPrettyPrint($data, $prefix = '') {
    $php = 'array(' . PHP_EOL;
    foreach ($data as $key => $value) {
      $php .= $prefix . '  ' . var_export($key, true) . ' => ';
      if (is_array($value)) {
        $php .= AppConfig::phpPrettyPrint($value, $prefix . '  ');
      }
      else {
        $php .= var_export($value, true);
      }
      $php .= ',' . PHP_EOL;
    }
    return $php . $prefix . ')';
  }

  /**
   * Save configuration to config-file
   * @return boolean True on success, false on failure
   */
  public function save() {
    if ($this->root !== $this) {
      return $this->root->save();
    }
    if ($this->updated == false) {
      return false;
    }
    if (!is_writable($this->file)) {
      return false;
    }
    $filePointer = fopen($this->file, 'w');
    if (!$filePointer)
      return false;
    $data = AppConfig::phpPrettyPrint($this->data);
    fwrite($filePointer, '<?php' . PHP_EOL . 'return ' . $data . ';' . PHP_EOL);
    fclose($filePointer);
    return true;
  }
  
  public function merge($array) {
  }
  
  /* arrayaccess implementation */
  
  /**
   * Whether or not a key exists.
   * @param string $name Key
   * @return bool True if it does, false otherwise
   */
  public function offsetExists($key) {
    return $this->exists($key);
  }
  
  /**
   * Get a value
   * @param string $name Key
   * @return mixed Value
   */
  public function offsetGet($key) {
    if (!isset($this->data[$key]) OR is_array($this->data[$key])) {
      return $this->getSubset($key);
    }
    return $this->data[$key];
  }
  
  /**
   * Associate a value with a key
   * @param string $name Key
   * @param mixed $value Value
   */
  public function offsetSet($key, $value) {
    if (is_null($key)) {
    }
    else {
      $this->set($key, $value);
    }
  }
  
  /**
   * Delete a key
   * @param string $name Key
   */
  public function offsetUnset($key) {
    $this->delete($key);
  }
}