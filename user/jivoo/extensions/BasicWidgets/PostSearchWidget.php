<?php
class PostSearchWidget extends Widget {
  
  protected $models = array('Post');
  
  protected $helpers = array('Html', 'Form');
  
  public function getDefaultTitle() {
    return '';
  }
  
  public function main($config) {
    return $this->fetch();
  }
}
