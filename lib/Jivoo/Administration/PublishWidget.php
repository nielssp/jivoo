<?php
class PublishWidget extends Widget {
  protected $helpers = array('Form');
  
  protected $options = array(
  	'record' => null,
    'title' => 'title',
    'content' => 'content',
    'route' => array()
  );
  
  public function main($options) {
    assume($options['record'] instanceof IBasicRecord);
    return $this->fetch();
  }
}