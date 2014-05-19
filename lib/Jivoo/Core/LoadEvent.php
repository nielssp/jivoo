<?php
/**
 * Event sent before and after an object has been loaded (e.g. LoadableModule,
 * Helper, Extension etc.)
 * @property-read string $class Name of object
 * @property-read bool $loaded Whether or not the object has been loaded
 * @property-read Module|null $object Object if loaded
 */
class LoadEvent extends Event {
  public $class;
  public $loaded = false;
  public $object;
  public function __construct($sender, $class, Module $object = null) {
    parent::__construct($sender);
    $this->class = $class;
    if (isset($object)) {
      $this->loaded = true;
      $this->object = $object;
    }
  }
}