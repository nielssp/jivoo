<?php
/**
 * A collection of key/value pairs
 * @package Core
 */
class Dictionary implements IDictionary {
  /**
   * @var array Key/value pairs
   */
  private $array = array();
  
  /**
   * @var bool Whether or not dictionary is read-only
   */
  private $readOnly = false;

  public function __get($key) {
    if (isset($this->array[$key])) {
      return $this->array[$key];
    }
    else {
      throw new DictionaryKeyInvalidException(tr('Invalid dictionary key: %1', $key));
    }
  }

  public function __set($key, $value) {
    if ($this->readOnly) {
      throw new DictionaryReadOnlyException(tr('Dictionary is read-only.'));
    }
    $this->array[$key] = $value;
  }

  public function __isset($key) {
    return isset($this->array[$key]);
  }

  public function __unset($key) {
    if ($this->readOnly) {
      throw new DictionaryReadOnlyException(tr('Dictionary is read-only.'));
    }
    unset($this->array[$key]);
  }

  public function isReadOnly() {
    return $this->readOnly;
  }

  /**
   * Construct a dictionary
   * @param array $array Key/value pairs to build dictionary from
   * @param bool $readOnly Whether or not this dictionary should be read-only
   */
  public function __construct($array = array(), $readOnly = false) {
    $this->array = $array;
    $this->readOnly = $readOnly;
  }
}
