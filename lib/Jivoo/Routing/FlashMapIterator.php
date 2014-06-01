<?php
class FlashMapIterator implements Iterator {
  
  private $flash;
  
  private $list;
  
  public function __construct(FlashMap $flash, $lists) {
    $this->flash = $flash;
    foreach ($lists as $type => $list) {
      $array = $list->toArray();
      foreach ($array as $index => $message) {
        $this->list[] = array($index, new FlashMessage($message, $type));
      }
    }
  }
  
  
  public function current() {
    $tuple = current($this->list);
    $index = $tuple[0];
    $type = $tuple[1]->type;
    unset($this->flash[$type][$index]);
    return $tuple[1];
  }
  
  public function next() {
    next($this->list);
  }
  
  public function key() {
    return key($this->list);
  }
  
  public function valid() {
    return key($this->list) !== null;
  }
  
  public function rewind() {
    reset($this->list);
  }
}