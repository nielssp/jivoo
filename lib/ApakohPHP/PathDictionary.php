<?php
/**
 * A collection of paths
 * @package ApakohPHP
 */
class PathDictionary extends Dictionary {
  private $basePath;
  private $defaultBasePath;

  public function __construct($basePath, $defaultBasePath = null) {
    $this->basePath = rtrim($basePath, '\\/');
    $this->defaultBasePath = $this->basePath;
    if (isset($defaultBasePath)) {
      $this->defaultBasePath = $defaultBasePath;
    }
  }

  public function __get($key) {
    if (!parent::__isset($key)) {
      return $this->defaultBasePath . '/' . $key;
    }
    $path = parent::__get($key);
    /** @todo Also check for other absolute paths.. E.g. C:\example\directory on Windows */
    if ($path == '' OR $path[0] == '/' OR $path[1] == ':') {
      return $path;
    }
    return $this->basePath . '/' . $path;
  }

  public function __set($key, $path) {
    parent::__set($key, str_replace('\\', '/', realpath($path)));
  }
}
