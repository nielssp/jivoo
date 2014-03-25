<?php
/**
 * A collection of paths
 * @package Core
 */
class PathDictionary extends Dictionary {
  /**
   * @var string Base path
   */
  private $basePath;
  
  /**
   * @var string Default base path
   */
  private $defaultBasePath;

  /**
   * Constructor
   * @param string $basePath Base path
   * @param string $defaultBasePath If defined, this will be used instead of
   * $basePath when keys are undefined
   */
  public function __construct($basePath, $defaultBasePath = null) {
    $this->basePath = rtrim($basePath, '\\/');
    $this->defaultBasePath = $this->basePath;
    if (isset($defaultBasePath)) {
      $this->defaultBasePath = $defaultBasePath;
    }
  }

  /**
   * Get the path associated with a key
   * 
   * If the key is undefined, the key will be appended to the default base path.
   * 
   * If the associated path is absolute, e.g. begins with '/' or 'C:/' then
   * only the path is returned, otherwise the path is appended to the base path.
   */
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

  /**
   * Associate a path with a key. Automatically converts '\\' to '/'.
   */
  public function __set($key, $path) {
    parent::__set($key, str_replace('\\', '/', realpath($path)));
  }
}
