<?php

class Dictionary {
  private $array = array();
  private $readOnly = FALSE;

  public function __get($key) {
    if (array_key_exists($key, $this->array)) {
      return $this->array[$key];
    }
    else {
      throw new DictionaryKeyInvalidException(tr('Invalid dictionary key.'));
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

  public function __construct($array = array(), $readOnly = FALSE) {
    $this->array = $array;
    $this->readOnly = $readOnly;
  }
}

class DictionaryKeyInvalidException extends Exception { }
class DictionaryReadOnlyException extends Exception { }
