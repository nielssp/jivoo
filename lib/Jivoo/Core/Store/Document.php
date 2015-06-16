<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

/**
 * Representation of semi-structured data such as a configuration ({@see Config})
 * or a state object ({@see State}). 
 */
class Document implements \ArrayAccess, \IteratorAggregate {
  /**
   * @var string|null
   */
  private $emptySubset = null;
  
  /**
   * @var array Associatve array of key-value pairs for current subset
   */
  protected $data = array();
  
  /**
   * @var bool Whether to save default values.
   */
  protected $saveDefaults = true;
  
  /**
   * @var bool True if document has been changed.
   */
  protected $updated = false;
  
  /**
   * @var Document|null Root document.
   */
  protected $root = null;
  
  /**
   * @var Document|null Parent document.
   */
  protected $parent = null;
  
  /**
   * Construct empty document.
   */
  public function __construct() {
    $this->root = $this;
  }
  
  /**
   * Convert to associative array.
   * @return array Array.
   */
  public function toArray() {
    return $this->data;
  }
  
  /**
   * Convert to document.
   * @return Document Document.
   */
  public function toDocument() {
    $doc = new Document();
    $doc->data = $this->data;
    return $doc;
  }

  /**
   * Get a subdocument.
   * @param string $key Key.
   * @return Document A subset.
   */
  public function getSubset($key) {
    if (isset($this->emptySubset))
      $this->createTrueSubset();
    $doc = $this->createEmpty();
    if (!isset($this->data[$key]) or !is_array($this->data[$key])) {
      $doc->data = null;
      $doc->emptySubset = $key;
    }
    else {
      $doc->data =& $this->data[$key];
    }
    $doc->parent = $this;
    $doc->root = $this->root;
    return $doc;
  }
  
  /**
   * Create empty document.
   * @return Document Empty document.
   */
  protected function createEmpty() {
    return new Document();
  }
  
  /**
   * Create actual subset.
   */
  protected function createTrueSubset() {
    $this->parent->data[$this->emptySubset] = array();
    $this->data =& $this->parent->data[$this->emptySubset];
    $this->emptySubset = null;
  }
  
  /**
   * Update a document  key.
   * @param string $key The document key to access.
   * @param mixed $value The value to associate with the key.
   */
  public function set($key, $value) {
    if (isset($this->emptySubset))
      $this->createTrueSubset();
    $oldValue = null;
    if (isset($this->data[$key])) {
      $oldValue = $this->data[$key];
    }
    if (isset($key) and isset($value) and $key != '') {
      $this->data[$key] = $value;
    }
    else {
      $this->data[$key] = null;
    }
    if (!$this->root->updated and $oldValue !== $value) {
      $this->root->updated = true;
    }
  }
  
  /**
   * Set default value.
   * @param string $key Document key.
   * @param mixed $value Value.
   */
  public function setDefault($key, $value) {
    if ($this->saveDefaults) {
      if (!$this->exists($key))
        $this->set($key, $value);
    }
    else {
      // ??
    }
  }
  
  
  /**
   * Delete a configuration key
   * @param string $key The configuration key to delete
   */
  public function delete($key) {
    if (isset($this->data[$key])) {
      unset($this->data[$key]);
      $this->root->updated = true;
    }
  }
  
  /**
   * Retreive value of a document key. Returns the default value if
   * the key is not found or if the type of the found value does not match the
   * type of the defuault value.
   * @param string $key Document key.
   * @param mixed $default Optional default value.
   * @return mixed Content of document key.
   */
  public function get($key, $default = null) {
    if (isset($this->data[$key])) {
      $value = $this->data[$key];
    }
    else {
      if (isset($default))
        $this->setDefaul($key, $default);
      return $default;
    }
    if (isset($default)) {
      if (gettype($default) !== gettype($value)) {
        $this->setDefaul($key, $default);
        return $default;
      }
    }
    return $value;
  }
  
  /**
   * Check if a key exists
   * @param string $key Document key
   * @return bool True if it exists false if not
   */
  public function exists($key) {
    return isset($this->data[$key]);
  }
  

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
    if (!isset($this->data[$key]) or is_array($this->data[$key])) {
      return $this->getSubset($key);
    }
    return $this->data[$key];
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
  
  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new MapIterator($this->data);
  }
}