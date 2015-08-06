<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Store;

use Jivoo\Core\MapIterator;
use Jivoo\InvalidPropertyException;

/**
 * Representation of semi-structured data such as a configuration ({@see Config})
 * or a state object ({@see State}).
 * @property-read Document|null $parent Parent document if any.
 * @property-read Document $root Root document.
 * @property-write array $defaults Set multiple default values.
 * @property-write array $override Set multiple values.
 */
class Document implements \ArrayAccess, \IteratorAggregate {
  /**
   * @var string|null
   */
  private $emptySubset = null;
  
  /**
   * @var array Associatve array of key-value pairs for current subset.
   */
  protected $data = array();
  
  /**
   * @var array Associative array of default values for current subset.
   */
  protected $defaultData = array();
  
  /**
   * @var bool Whether to save default values.
   */
  protected $saveDefaults = true;
  
  /**
   * @var bool True if document has been changed (only has meaning in the
   * root document).
   */
  protected $updated = false;
  
  /**
   * @var Document Root document.
   */
  protected $root;
  
  /**
   * @var Document|null Parent document if any.
   */
  protected $parent = null;
  
  /**
   * Construct empty document.
   */
  public function __construct() {
    $this->root = $this;
  }
  
  /**
   * Get the value of a property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property undefined.
   */
  public function __get($property) {
    switch ($property) {
      case 'parent':
      case 'root':
        return $this->$property;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Set the value of a property.
   * @param string $property Property name.
   * @param mixed $value Value.
   * @throws InvalidPropertyException If property undefined.
   */
  public function __set($property, $value) {
    switch ($property) {
      case 'defaults':
        assume(is_array($value));
        $array = $value;
        foreach ($array as $key => $value)
          $this->setDefault($key, $value);
        return;
      case 'override':
        assume(is_array($value));
        $array = $value;
        foreach ($array as $key => $value)
          $this->setRecursive($key, $value);
        return;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Convert to associative array.
   * @return array Array.
   */
  public function toArray() {
    if ($this->saveDefaults)
      return $this->data;
    return $this->toDocument()->toArray();
  }
  
  /**
   * Convert to document.
   * @return Document Document.
   */
  public function toDocument() {
    $doc = new Document();
    $doc->data = $this->data;
    $doc->defaults = $this->defaultData;
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
    if (!$this->saveDefaults) {
      if (!isset($this->defaultData[$key]) or !is_array($this->defaultData[$key]))
        $this->defaultData[$key] = array();
      $doc->defaultData =& $this->defaultData[$key];
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
    assume(is_scalar($key));
    if (isset($this->emptySubset))
      $this->createTrueSubset();
    $oldValue = null;
    if (isset($this->data[$key])) {
      $oldValue = $this->data[$key];
    }
    if (isset($key) and isset($value) and $key !== '') {
      $this->data[$key] = $value;
    }
    else {
      $this->data[$key] = null;
    }
    if (!$this->root->updated and $oldValue !== $value) {
      $this->root->updated = true;
      $this->root->update();
    }
  }
  
  /**
   * This method is called (on the root document) whenever changes are
   * made in the document.
   */
  protected function update() { }
  
  /**
   * Update a subdocument recursively.
   * @param string $key The document key to access.
   * @param mixed $value The value or subarray to associate with the key.
   */
  public function setRecursive($key, $value) {
    if (!is_array($value)) {
      $this->set($key, $value);
      return;
    }
    $subset = $this->getSubset($key);
    $array = $value;
    foreach ($array as $key => $value)
      $subset->setRecursive($key, $value);
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
      else if (is_array($value))
        $this[$key]->defaults = $value;
    }
    else {
      if (!isset($this->defaultData[$key]))
        $this->defaultData[$key] = $value;
      else if (is_array($value))
        $this[$key]->defaults = $value;
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
      $this->root->update();
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
        $this->setDefault($key, $default);
      else if (isset($this->defaultData[$key]))
        $default = $this->defaultData[$key];
      return $default;
    }
    if (isset($default)) {
      if (gettype($default) !== gettype($value)) {
        $this->setDefault($key, $default);
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
   * @param string $key Key
   * @return bool True if it does, false otherwise
   */
  public function offsetExists($key) {
    return $this->exists($key);
  }
  
  /**
   * Get a value
   * @param string $key Key
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
   * @param string $key Key
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
   * @param string $key Key
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