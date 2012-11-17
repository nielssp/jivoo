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

  /**
   * Parse data string into data array
   *
   * @param string $data Data string
   * @param bool $serialize Whether or not to unserialize data that appears to be serialized
   * @param bool $associative Return an associative array of keys and values
   * @param bool $tree Create a tree
   * @return array Data fields in an array
   */
  public static function parseData($data, $serialize = false, $associative = true,  $tree = true) {
    $fields = explode('|', $data);
    $dataArray = array();
    $dataKey = '';
    foreach ($fields as $key => $value) {
      if ($key & 1) {
        if ($associative) {
          if ($tree) {
            $dataKeyParts = explode('.', $dataKey);
            $dataKey = array_pop($dataKeyParts);
            $arrayRef =& $dataArray;
            foreach ($dataKeyParts as $dataKeyPart) {
              if (!isset($arrayRef[$dataKeyPart]) OR !is_array($arrayRef[$dataKeyPart])) {
                $arrayRef[$dataKeyPart] = array();
              }
              $arrayRef =& $arrayRef[$dataKeyPart];
            }
            $arrayRef[$dataKey] = html_entity_decode($value, ENT_NOQUOTES, 'UTF-8');
            if ($serialize AND isSerialized($arrayRef[$dataKey]))
              $arrayRef[$dataKey] = unserialize($arrayRef[$dataKey]);
          }
          else {
            $dataArray[$dataKey] = html_entity_decode($value, ENT_NOQUOTES, 'UTF-8');
            if ($serialize AND isSerialized($dataArray[$dataKey]))
              $dataArray[$dataKey] = unserialize($dataArray[$dataKey]);
          }
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
   * @param bool $serialize Wether or not to serialize arrays, if false then
   * arrays and objects will not be included in data
   * @param bool $associative Create from associatve array
   * @param bool $tree Create from tree (recursive)
   * @param string $parent Parent key
   * @return string Data string
   */
  public static function compileData($fields, $serialize = false, $associative = true, $tree = true, $parent = '') {
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
        if ((is_array($value) OR is_object($value)) AND $serialize AND !$tree) {
          $value = serialize($value);
        }
        if ($tree) {
          if (is_array($value)) {
            $data .= Configuration::compileData($value, false, true, true, $parent . $key . '.');
          }
          else if (isset($value)) {
            $value = str_replace('|', '&#124;', htmlentities($value, ENT_NOQUOTES, 'UTF-8'));
            $data .= $parent . $key . '|' . $value . "|\n";
          }
        }
        else if (isset($value)) {
          $value = str_replace('|', '&#124;', htmlentities($value, ENT_NOQUOTES, 'UTF-8'));
          $data .= $key . '|' . $value . "|\n";
        }
      }
    }
    return $data;
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
