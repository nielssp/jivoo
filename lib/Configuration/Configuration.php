<?php
// Module
// Name           : Configuration
// Version        : 0.2.0
// Description    : The PeanutCMS configuration system
// Author         : PeanutCMS
// Dependencies   : Errors

/**
 * Represents a configuration file or subset
 *
 * Implements arrayaccess, so the []-operator can be used
 * to get and set configuration values.
 * @package PeanutCMS
 * @subpackage Modules
 */
class Configuration extends ModuleBase implements arrayaccess {

  private $data = array();

  private $parentKey = '';

  private $file;

  private $save = true;

  /**
   * Module initializer
   * @param string $cfgFile Configuration file
   * @param Configuration $subsetOf Configuration to be a subset of
   */
  protected function init($cfgFile = null, Configuration $subsetOf = null) {
    if (!isset($cfgFile)) {
      $cfgFile = p(CFG . 'config.php');
    }
    $this->file = $cfgFile;
    if (isset($subsetOf)) {
      $this->data =& $subsetOf->data; 
      return;
    }
    if (!is_readable($this->file)) {
      // Attempt to create configuration-file
      $file = fopen($this->file, 'w');
      if (!$file) {
        Errors::fatal(tr('Fatal error'), tr('%1 is missing or inaccessible and could not be created', $this->file));
      }
      fwrite($file, '<?php' . PHP_EOL . 'return array();' . PHP_EOL);
      fclose($file);
    }
    if (!is_writable($this->file)) {
      new GlobalWarning(tr('%1 is not writable', $this->file), 'settings-writable');
    }
    $this->data = include $this->file;
  }

  /**
   * Get a subset of the current configuration
   * @param string $key The key of the subset
   * @return Configuration A subset
   */
  public function getSubset($key) {
    $config = new Configuration(array('Errors' => $this->m->Errors), $this->Core, $this->file, $this);
    $config->parentKey = $this->realKey($key);
    return $config;
  }
  
  private function realKey($key) {
    if ($this->parentKey != '') {
      $key = $this->parentKey . ($key != '' ? '.' . $key : '');
    }
    return $key;
  }

  private function &getDataReference($key) {
    $key = $this->realKey($key);
    $keyArray = explode('.', $key);
    $arrayRef =& $this->data;
    foreach ($keyArray as $part) {
      if (!empty($part)) {
        if (!is_array($arrayRef)) {
          $arrayRef = array();
        }
        $arrayRef =& $arrayRef[$part];
      }
    }
    return $arrayRef;
  }

  /**
   * Get the current configuration as an array
   * @return array Configuration
   */
  public function getArray() {
    return $this->getDataReference('');
  }

  /**
   * Update a configuration key
   *
   * @param string $key The configuration key to access
   * @param mixed $value The variable to associate with the key. Could be a string/array/object etc.
   * @return bool True if successful, false if not
   */
  public function set($key, $value) {
    $ref = &$this->getDataReference($key);
    if (isset($key) AND isset($value) AND $key != '')
      $ref = $value;
    else
      $ref = null;
    return $this->save();
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
        $php .= Configuration::phpPrettyPrint($value, $prefix . '  ');
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
  private function save() {
    if ($this->save == false) {
      return false;
    }
    if (!is_writable($this->file)) {
      return false;
    }
    $filePointer = fopen($this->file, 'w');
    if (!$filePointer)
      return false;
    $data = Configuration::phpPrettyPrint($this->data);
    fwrite($filePointer, '<?php' . PHP_EOL . 'return ' . $data . ';' . PHP_EOL);
    fclose($filePointer);
    return true;
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
    return $this->set($key, null);
  }


  /**
   * Set default values.
   * @param string|array Either a key as a string or an array of key/value pairs
   * @param mixed $value Value
   */
  public function setDefault($key, $value = null) {
    if (is_array($key)) {
      $array = $key;    
      $this->save = false;
      $changed = false;
      foreach ($array as $key => $value) {
        if (!$this->exists($key)) {
          $this->set($key, $value);
          $changed = true;
        }
      }
      $this->save = true;
      if ($changed) {
        $this->save();
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
    $ref = &$this->getDataReference($key);
    if (!isset($ref)) {
      return $arrayOnly ? array() : false;
    }
    if ($arrayOnly && !is_array($ref)) {
      return array();
    }
    return $ref;
  }

  /**
   * Check if a key exists
   *
   * @param string $key Configuration key
   * @return bool True if it exists false if not
   */
  public function exists($key) {
    /** @todo Do something less expensive for this... */
    $ref = &$this->getDataReference($key);
    return isset($ref);
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
    $value = $this->get($key);
    if (is_array($value) OR $value === false) {
      return $this->getSubset($key);
    }
    return $value;
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
