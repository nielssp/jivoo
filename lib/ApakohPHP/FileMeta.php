<?php
/**
 * Accessing meta data in source files
 * 
 * @package ApakohPHP
 */
class FileMeta {
  private function __construct() {
  }

  public static function read($file, $caching = null) {
    if (!isset($caching)) {
      $caching = false;
    }
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
    if (!isset($metaData['version'])) {
      $metaData['version'] = '0.0.0';
    }
    if ($caching AND is_writable(p(TMP))) {
      $cacheFile = fopen(p(TMP . $uid), 'w');
      if ($cacheFile) {
        fwrite($cacheFile, serialize($metaData));
        fclose($cacheFile);
      }
    }
    return $metaData;
  }

  public static function readDependencies($dependencies) {
    $depArray = explode(' ', $dependencies);
    $result = array('modules' => array(), 'extensions' => array(),
      'php' => array()
    );
    foreach ($depArray as $dependency) {
      if (!empty($dependency)) {
        if (strpos($dependency, ';') === false) {
          if (($matches = self::matchDependencyVersion($dependency)) !== false) {
            $matches[1] = $matches[1];
            if (!isset($result['modules'][$matches[1]])) {
              $result['modules'][$matches[1]] = array();
            }
            $result['modules'][$matches[1]][$matches[2]] = $matches[3];
          }
          else {
            $result['modules'][$dependency] = array();
          }
        }
        else {
          $split = explode(';', $dependency, 2);
          if ($split[0] == 'ext') {
            if (($matches = self::matchDependencyVersion($split[1])) !== false) {
              $matches[1] = $matches[1];
              if (!isset($result['extensions'][$matches[1]])) {
                $result['extensions'][$matches[1]] = array();
              }
              $result['extensions'][$matches[1]][$matches[2]] = $matches[3];
            }
            else {
              $result['extensions'][$split[1]] = array();
            }
          }
          else if ($split[0] == 'php') {
            if (($matches = self::matchDependencyVersion($split[1])) !== false) {
              if (!isset($result['php'][$matches[1]])) {
                $result['php'][$matches[1]] = array();
              }
              $result['php'][$matches[1]][$matches[2]] = $matches[3];
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

  public static function matchDependencyVersion($dependency) {
    if (preg_match('/^(.+?)(<>|<=|>=|==|!=|<|>|=)(.+)/', $dependency, $matches)
        == 1) {
      return $matches;
    }
    else {
      return false;
    }
  }

  public static function compareDependencyVersions($versionStr, $versionInfo) {
    if (!is_array($comparisonArray)) {
      return false;
    }
    foreach ($comparisonArray as $operator => $version) {
      if (!version_compare($versionStr, $version, $operator)) {
        return false;
      }
    }
    return true;
  }
}
