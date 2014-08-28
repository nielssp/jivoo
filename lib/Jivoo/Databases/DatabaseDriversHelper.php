<?php
class DatabaseDriversHelper extends Helper {
  /**
   * Get information about a database driver.
   *
   * The returned information array is of the format:
   * <code>
   * array(
   *   'driver' => ..., // Driver name (string)
   *   'name' => ..., // Formal name, e.g. 'MySQL' instead of 'MySql' (string)
   *   'requiredOptions' => array(...), // List of required options (string[])
   *   'optionalOptions' => array(...), // List of optional options (string[])
   *   'isAvailable' => ..., // Whether or not driver is available (bool)
   *   'missingExtensions => array(...) // List of missing extensions (string[])
   * )
   * </code>
   * @param string $driver Driver name
   * @return array|nulll Driver information as an associative array or null if
   * not found
   */
  public function checkDriver($driver) {
    if (!file_exists($this->p('Databases', 'Drivers/' . $driver . '/' . $driver . 'Database.php'))) {
      return null;
    }
    $meta = FileMeta::read($this->p('Databases', 'Drivers/' . $driver . '/' . $driver . 'Database.php'));
    if (!isset($meta['required'])) {
      $meta['required'] = '';
    }
    $missing = array();
    foreach ($meta['dependencies']['php'] as $dependency => $versionInfo) {
      if (!extension_loaded($dependency)) {
        $missing[] = $dependency;
      }
    }
    return array('driver' => $driver, 'name' => $meta['name'],
      'requiredOptions' => explode(' ', $meta['required']),
      'optionalOptions' => explode(' ', $meta['optional']),
      'isAvailable' => count($missing) < 1, 'missingExtensions' => $missing
    );
  }
  
  /**
   * Get an array of all drivers and their information
   * @return array An associative array of driver names and driver information
   * as returned by {@see Database::checkDriver()}
   */
  public function listDrivers() {
    $drivers = array();
    $files = scandir($this->p('Databases', 'Drivers'));
    if ($files !== false) {
      foreach ($files as $driver) {
        if (is_dir($this->p('Databases', 'Drivers/' . $driver))) {
          if ($driverInfo = $this->checkDriver($driver)) {
            $drivers[$driver] = $driverInfo;
          }
        }
      }
    }
    return $drivers;
  }
}