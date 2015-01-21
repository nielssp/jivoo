<?php
/**
 * Represents a configuration file or subset
 *
 * Implements arrayaccess, so the []-operator can be used
 * to get and set configuration values.
 * @package Jivoo\Core
 * @property-read string $file File name of configuration file.
 * @property-read AppConfig $parent Get parent configuration
 * @property-write array $defaults Set default key-value pairs
 * @property-write array $override Set override key-value pairs 
 */
class AppConfig implements arrayaccess, IteratorAggregate {
  
  private $emptySubset = null;
  
  /**
   * @var array Associatve array of key-value pairs for current subset
   */
  private $data = array();

  /**
   * @var array Associative array of virtual data (not saved)
   */
  private $virtual = array();

  /**
   * @var string File name of configuration file
   */
  private $file;
  
  /**
   * @var string File type
   */
  private $type = null;

  /**
   * @var bool True if configuration has been updated
   */
  private $updated = false;

  /**
   * @var AppConfig|null Root configuration
   */
  private $root = null;
  
  /**
   * @var AppConfig|null Parent configuration
   */
  private $parent = null;
  
  /**
   * Constructor
   * @param string $configFile File name of configuration file
   * @param string $fileType Configuration file type. Supported types are: 'php'
   * and 'json'. Default is to use file extension.
   * @throws UnsupportedConfigurationFormatException If file format is not PHP
   * or JSON.
   */
  public function __construct($configFile = null, $type = null) {
    $this->root = $this;
    if (isset($configFile)) {
      if (!isset($type)) {
        $type = Utilities::getFileExtension($configFile);
      }
      $this->type = $type;
      $this->file = $configFile;
      if (file_exists($configFile)) {
        switch ($this->type) {
          case 'php':
            /** @TODO Temporary work-around for opcode caching */
            $content = file_get_contents($this->file);
            $content = str_replace('<?php', '', $content);
            $this->data = eval($content);
//         $this->data = include $configFile;
            break;
          case 'json':
            $content = file_get_contents($this->file);
            $this->data = Json::decode($content);
            break;
          default:
            throw new UnsupportedConfigurationFormat(
              tr('Unsupported file format: "%1"', $this->type)
            );
        }
      }
    }
  }
  
  /**
   * Destructor will attempt to save unsaved configuration data
   * @uses Logger::error() to log an error if unable to save
   */
  public function __destruct() {
    if (!isset($this->parent)) {
      if (!$this->save()) {
        Logger::error(tr('Unable to save config file: %1', $this->file));
      }
    }
  }
  
  /**
   * Get the value of a property.
   * @param string $property Property nam.
   * @return mixed Value.
   * @throws InvalidPropertyException If property undefined.
   */
  public function __get($property) {
    switch ($property) {
      case 'file':
        return $this->root->file;
      case 'parent':
        return $this->$property;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Set the value of a property.
   * @param string $property Property name.
   * @param mixed $value Value.
   * @throws InvalidPropertyException If property undefined.
   */
  public function __set($property, $value) {
    switch ($property) {
      case 'defaults':
        $this->setDefaults(is_array($value) ? $value : array());
        return;
      case 'override':
        $this->setMultiple(is_array($value) ? $value : array());
        return;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Get a subset AppConfig.
   * @param string $key Key.
   * @return AppConfig A subset.
   */
  public function getSubset($key) {
    if (isset($this->emptySubset))
      $this->createTrueSubset();
    $config = new AppConfig();
    if (!isset($this->data[$key])) {
      $config->data = null;
      $config->emptySubset = $key;
    }
    else {
      $config->data =& $this->data[$key];
    }
    $config->parent = $this;
    $config->root = $this->root;
    return $config;
  }
  
  /**
   * Create actual subset.
   */
  private function createTrueSubset() {
    $this->parent->data[$this->emptySubset] = array();
    $this->data =& $this->parent->data[$this->emptySubset];
    $this->emptySubset = null;
  }
  
  /**
   * Update a configuration key
   * @param string $key The configuration key to access
   * @param mixed $value The variable to associate with the key. Could be a string/array/object etc.
   */
  public function set($key, $value) {
    if (isset($this->emptySubset))
      $this->createTrueSubset();
    $oldValue = null;
    if (isset($this->data[$key])) {
      $oldValue = $this->data[$key];
    }
    if (isset($key) AND isset($value) AND $key != '') {
      $this->data[$key] = $value;
    }
    else {
      $this->data[$key] = null;
    }
    if (!$this->root->updated AND $oldValue !== $value) {
      $this->root->updated = true;
    }
  }
  

  /**
   * Delete a configuration key
   * @param string $key The configuration key to delete
   */
  public function delete($key) {
    if (isset($this->data[$key])) {
      unset($this->data[$key]);
      $this->root->updated = true;
    }
  }
  
  /**
   * Set virtual data (this key-value pair will not be saved)
   * @param string $key Key
   * @param mixed $value Value
   */
  public function setVirtual($key, $value) {
    $this->virtual[$key] = $value;
  }
  
  /**
   * Set default values.
   * @param string|array Either a key as a string or an array of key/value pairs
   * @param mixed $value Value
   */
  public function setDefaults($key, $value = null) {
    if (isset($this->emptySubset))
      $this->createTrueSubset();
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
   * Override values
   * @param array $array Associative array
   */
  public function setMultiple($array) {
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $this[$key]->setMultiple($value);
      }
      else {
        $this->set($key, $value);
      }
    }
  }
  
  /**
   * Return the value of a configuration key
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
  
  /**
   * Get as array.
   * @return array Configuration as an associative array.
   */
  public function getArray() {
    return $this->data;
  }
  
  /**
   * Check if a key exists
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
    if (is_array($data) and array_diff_key($data, array_keys(array_keys($data)))) {
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
    }
    else {
      foreach ($data as $value) {
        $php .= $prefix . '  ';
        if (is_array($value)) {
          $php .= AppConfig::phpPrettyPrint($value, $prefix . '  ');
        }
        else {
          $php .= var_export($value, true);
        }
        $php .= ',' . PHP_EOL;
      }
    }
    return $php . $prefix . ')';
  }
  
  /** 
   * Touch the configuration file (attempt to create it if it doesn't exist)
   * @return boolean True if file exists and is writable, false otherwise
   */
  public function touch() {
    if ($this->root !== $this) {
      return $this->root->touch();
    }
    $filePointer = fopen($this->file, 'w');
    if (!$filePointer)
      return false;
    fclose($filePointer);
    return true;
  }
  
  
  /**
   * Create configuration file content
   * @return string PHP source
   */
  public function prettyPrint() {
    if ($this->root !== $this) {
      return $this->root->prettyPrint();
    }
    return AppConfig::phpPrettyPrint($this->data);
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
      return true;
    }
    // The following returns false when file doesn't exist, even if it can be
    // created:
//     if (!is_writable($this->file)) {
//       return false;
//     }
    if (!is_dir(dirname($this->file)))
      return false;
    $filePointer = fopen($this->file, 'w');
    if (!$filePointer)
      return false;
    $data = AppConfig::phpPrettyPrint($this->data);
    fwrite($filePointer, '<?php' . PHP_EOL . 'return ' . $data . ';' . PHP_EOL);
    fclose($filePointer);

//     opcache_invalidate($this->file);
//     apc_delete_file($this->file);
    $this->updated = false;
    return true;
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
    if (isset($this->virtual[$key])) {
      return $this->virtual[$key];
    }
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
      if (isset($this->virtual[$key])) {
        $this->virtual[$key] = $value;
      }
      else {
        $this->set($key, $value);
      }
    }
  }
  
  /**
   * Delete a key
   * @param string $name Key
   */
  public function offsetUnset($key) {
    $this->delete($key);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new MapIterator($this->data);
  }
}

/**
 * A configuration file format is not supported
 * @package Jivoo\Core
 */
class UnsupportedConfigurationFormatException extends Exception { }
