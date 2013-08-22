<?php
/**
 * Accessing meta data in source files
 * @package Core
 */
class FileMeta {
  private function __construct() {
  }

  /**
   * Read metadata from a file
   * @todo Reimplement caching
   * @param string $file File path
   * @param bool $caching Whether to use caching
   * @return array|false An associative array of meta data or false on failure
   */
  public static function read($file, $caching = false) {
    $uid = md5($file);
    if ($caching AND file_exists(p(TMP . $uid))) {
      $serialized = file_get_contents(p(TMP . $uid));
      return unserialize($serialized);
    }
    $file = fopen($file, 'r');

    if (!$file) {
      return false;
    }

    $readingMeta = false;

    $metaData = array();
    $lines = 0;
    $currentKey = null;
    while ($line = fgets($file)) {
      $trimmed = trim($line);
      if (substr($trimmed, 0, 2) != '//') {
        if ($readingMeta) {
          break;
        }
        $lines++;
        if ($lines > 5) {
          return false;
        }
        continue;
      }
      $trimmed = trim(substr($trimmed, 2));
      if ($readingMeta) {
        $split = explode(':', $trimmed, 2);
        if (count($split) > 1) {
          $currentKey = strtolower(trim($split[0]));
          $metaData[$currentKey] = trim($split[1]);
        }
        else if ($currentKey != null) {
          $metaData[$currentKey] = trim(
            $metaData[$currentKey] . ' ' . trim($split[0]));
        }
      }
      else {
        $type = strtolower($trimmed);
        if ($type == 'module' OR $type == 'database' OR $type == 'theme'
            OR $type == 'extension') {
          $readingMeta = true;
          $metaData['type'] = $type;
        }
      }
    }
    fclose($file);
    if (!$readingMeta) {
      return false;
    }
    if (!isset($metaData['dependencies'])) {
      $metaData['dependencies'] = '';
    }
    $metaData['dependencies'] = self::readDependencies(
      $metaData['dependencies']);
//    if (!isset($metaData['version'])) {
//      $metaData['version'] = '0.0.0';
//    }
    if ($caching AND is_writable(p(TMP))) {
      $cacheFile = fopen(p(TMP . $uid), 'w');
      if ($cacheFile) {
        fwrite($cacheFile, serialize($metaData));
        fclose($cacheFile);
      }
    }
    return $metaData;
  }

  /**
   * Read a string of dependencies and return an array
   *
   * The array will be of the format:
   *    array(
   *      'modules' => array(
   *        ...
   *      ),
   *      'extensions' => array(
   *        ...
   *      ),
   *      'php' => array(
   *        ...
   *      )
   *    )
   *
   * @param string $dependencies A space-separated list of dependencies
   * @return array Associative array
   */
  public static function readDependencies($dependencies) {
    $depArray = explode(' ', $dependencies);
    $result = array( 
      'modules' => array(),
      'extensions' => array(),
      'php' => array()
    );
    foreach ($depArray as $dependency) {
      if (!empty($dependency)) {
        if (strpos($dependency, ';') === false) {
          if (($matches = self::matchDependencyVersion($dependency)) !== false) {
            if (!isset($result['modules'][$matches[0]])) {
              $result['modules'][$matches[0]] = array();
            }
            $result['modules'][$matches[0]][$matches[1]] = $matches[2];
          }
          else {
            $result['modules'][$dependency] = array();
          }
        }
        else {
          $split = explode(';', $dependency, 2);
          if ($split[0] == 'ext') {
            if (($matches = self::matchDependencyVersion($split[1])) !== false) {
              if (!isset($result['extensions'][$matches[0]])) {
                $result['extensions'][$matches[0]] = array();
              }
              $result['extensions'][$matches[0]][$matches[1]] = $matches[2];
            }
            else {
              $result['extensions'][$split[1]] = array();
            }
          }
          else if ($split[0] == 'php') {
            if (($matches = self::matchDependencyVersion($split[1])) !== false) {
              if (!isset($result['php'][$matches[0]])) {
                $result['php'][$matches[0]] = array();
              }
              $result['php'][$matches[0]][$matches[1]] = $matches[2];
            }
            else {
              $result['php'][$split[1]] = array();
            }
          }
        }
      }
    }
    return $result;
  }

  /**
   * Will split a dependency of the format Database>=0.1.5 into name, operator and version
   *
   * The following operators are supported: <>, <=, >=, ==, !=, <, >, =
   *
   * @param string $dependency A dependency e.g. 'Core<0.1'
   * @return string[]|false An array e.g. array('Core', '<', '0.1') or false if wrong format
   */
  public static function matchDependencyVersion($dependency) {
    if (preg_match('/^(.+?)(<>|<=|>=|==|!=|<|>|=)(.+)/', $dependency, $matches)
        == 1) {
      array_shift($matches);
      return $matches;
    }
    else {
      return false;
    }
  }

  /**
   * Compare a version string with a dependency version array as returned by readDependencies
   * @param string $versionStr A version string e.g. '1.5.6'
   * @param array $versionInfo An associative array of operators/versions
   * e.g. array('>=' => '1.5.0')
   * @return bool True if all conditions are met, false otherwise
   * @uses version_compare() to compare versions
   */
  public static function compareDependencyVersions($versionStr, $versionInfo) {
    if (!is_array($versionInfo)) {
      return false;
    }
    foreach ($versionInfo as $operator => $version) {
      if (!version_compare($versionStr, $version, $operator)) {
        return false;
      }
    }
    return true;
  }
}
