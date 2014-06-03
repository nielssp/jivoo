<?php
class WidgetHelper extends Helper {
  
  protected $modules = array('Widgets');
  
  public function widget($name, $options = array()) {
    $widget = $this->m->Widgets->getWidget($name);
    return $widget->main($options);
  }
}