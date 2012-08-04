<?php
// Module
// Name           : Configuration
// Version        : 0.2.0
// Description    : The PeanutCMS configuration system
// Author         : PeanutCMS
// Dependencies   : Errors

class Configuration extends ModuleBase implements arrayaccess {

  private $data = array();

  private $parentKey = '';

  private $file;

  private $save = TRUE;

  protected function init($cfgFile = NULL, Configuration $subsetOf = NULL) {
    if (!isset($cfgFile)) {
      $cfgFile = p(CFG . 'config.cfg.php');
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
      fwrite($file, "<?php exit; ?>\n");
      fclose($file);
    }
    if (!is_writable($this->file)) {
      new GlobalWarning(tr('%1 is not writable', $this->file), 'settings-writable');
    }
    $fileContent = file_get_contents($this->file);
    if ($fileContent === FALSE) {
      Errors::fatal(tr('Fatal error'), tr('%1 is missing or inaccessible', $this->file));
    }
    $file = explode('?>', $fileContent);
    $this->data = Configuration::parseData($file[1]);
  }

  public function getSubset($key) {
    $config = new Configuration(array($this->m->Errors), $this->Core, $this->file, $this);
    $config->parentKey = $key;
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
      $ref = NULL;
    return $this->save();
  }

  private function save() {
    if ($this->save == FALSE) {
      return FALSE;
    }
    if (!is_writable($this->file))
      return FALSE;
    $filePointer = fopen($this->file, 'w');
    if (!$filePointer)
      return FALSE;
    $data = Configuration::compileData($this->data);
    fwrite($filePointer, "<?php exit(); ?>\n" . $data);
    fclose($filePointer);
    return TRUE;
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


  public function setDefault($key, $value = NULL) {
    if (is_array($key)) {
      $array = $key;    
      $this->save = FALSE;
      $changed = FALSE;
      foreach ($array as $key => $value) {
        if (!$this->exists($key)) {
          $this->set($key, $value);
          $changed = TRUE;
        }
      }
      $this->save = TRUE;
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
   * @return mixed The content of the configuration key or false if key
   * doesn't exist
   */
  public function get($key = '', $arrayOnly = FALSE) {
    $ref = &$this->getDataReference($key);
    if (!isset($ref)) {
      return $arrayOnly ? array() : FALSE;
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
   * @param bool $serialize Wether or not to unserialize data that appears to be serialized
   * @param bool $associative Return an associative array of keys and value
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
   * @param bool $serialize Wether or not to serialize arrays, if false then arrays
   *  and objects will not be included in data
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

  public function offsetExists($key) {
    return $this->exists($key);
  }
  
  public function offsetGet($key) {
    return $this->get($key);
  }
  
  public function offsetSet($key, $value) {
    if (is_null($key)) {
    }
    else {
      $this->set($key, $value);
    }
  }
  
  public function offsetUnset($key) {
    $this->delete($key);
  }
}
