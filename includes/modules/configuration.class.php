<?php
/* 
 * Configuraton
 *
 * @package PeanutCMS
 */

/**
 * Configuration class
 */
class Configuration {

  /**
   * Contains all configuration settings
   * @var array
   */
  var $settings;

  /**
   * PHP5-style constructor
   */
  function __construct() {
    global $PEANUT;
    $this->settings = array();
    if (!is_readable(PATH . DATA . 'settings.cfg.php')) {
      // Attempt to create configuration-file
      $file = fopen(PATH . DATA . 'settings.cfg.php', 'w');
      if (!$file)
        $PEANUT['errors']->fatal(tr('Fatal error'), tr('%1 is missing or inaccessible and could not be created', PATH . DATA. 'settings.cfg.php'));
      fwrite($file, "<?php exit; ?>\n");
      fclose($file);
    }
    if (!is_writable(PATH . DATA . 'settings.cfg.php'))
      $PEANUT['errors']->notification('warning', tr('%1 is not writable', PATH . DATA . 'settings.cfg.php'), true, 'settings-writable');
    $fileContent = file_get_contents(PATH . DATA . 'settings.cfg.php');
    if ($fileContent === FALSE)
      $PEANUT['errors']->fatal(tr('Fatal error'), tr('%1 is missing or inaccessible', PATH . DATA. 'settings.cfg.php'));
    $file = explode('?>', $fileContent);
    $this->settings = $this->parseData($file[1], true);
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
   * Update a configuration key
   *
   * @param string $key The configuration key to access
   * @param mixed $value The variable to associate with the key. Could be a string/array/object etc.
   * @return bool True if successful, false if not
   */
  function set($key, $value) {
    if (isset($key) AND isset($value) AND $key != '')
      $this->settings[$key] = $value;
    else
      unset($this->settings[$key]);

    if (!is_writable(PATH . DATA . 'settings.cfg.php'))
      return false;
    $file = fopen(PATH . DATA . 'settings.cfg.php', 'w');
    if (!$file)
      return false;
    $data = $this->compileData($this->settings, true);
    fwrite($file, "<?php exit(); ?>\n" . $data);
    fclose($file);
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
  function delete($key) {
    return $this->update($key, NULL);
  }

  /**
   * Return the value of a configuration key
   *
   * @param string $key Configuration key
   * @return mixed The content of the configuration key or false if key
   * doesn't exist
   */
  function get($key) {
    if (!isset($this->settings[$key]))
      return;
    return $this->settings[$key];
  }

  /**
   * Check if a key exists
   *
   * @param string $key Configuration key
   * @return bool True if it exists false if not
   */
  function exists($key) {
    return isset($this->settings[$key]);
  }

  /**
   * Parse data string into data array
   *
   * @param string $data Data string
   * @param bool $serialize Wether or not to unserialize data that appears to be serialized
   * @param bool $associative Return an associative array of keys and value
   * @return array Data fields in an array
   */
  function parseData($data, $serialize = false, $associative = true) {
    $fields = explode('|', $data);
    $dataArray = array();
    $dataKey = '';
    foreach ($fields as $key => $value) {
      if ($key & 1) {
        if ($associative) {
          $dataArray[$dataKey] = html_entity_decode($value, ENT_NOQUOTES, 'UTF-8');
          if ($serialize AND $this->isSerialized($dataArray[$dataKey]))
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
  function compileData($fields, $serialize = false, $associative = true) {
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

  /**
   * Check if a string is a serialized array (!)
   *
   * This function will only check
   *
   * @param string $str String
   * @return bool True if string is serialized
   */
  function isSerialized($str){
      if (!is_string($str))
        return false;
      if (trim($str) == "")
        return false;
      if (preg_match('/^(i|s|a|o|d):(.*);/si', $str) == 0)
        return false;
      return true;
  }

}
