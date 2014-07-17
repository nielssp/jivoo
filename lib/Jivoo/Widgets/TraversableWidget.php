<?php
abstract class TraversableWidget extends Widget implements IteratorAggregate {
  
  public function begin($options) {
    $options = array_merge($this->options, $options);
    $this->widget = $this;
    $this->__set('options', $options);
    $this->items = $this->getItems($options);
    $this->view->begin('widget-content');
  }
  
  public function end() {
    $this->view->end();
    return $this->main($this->__get('options'));
  }
  
  public function widget($options) {
    $this->begin($options);
    foreach ($this as $item) {
      echo $this->handle($item, $options);
    }
    return $this->end();
    return $this->main($options);
  }
    
  public abstract function handle($item, $options = array());
  
  /**
   * @return array|Traversable
   */
  protected abstract function getItems($options);
  
  public function getIterator() {
    if (is_array($this->items)) {
      return new ArrayIterator($this->items);
    }
    return $this->items;
  }
}