<?php
class WidgetHelper extends Helper {
  
  protected $modules = array('Widgets');
  
  public function widget($name, $options = array()) {
    $widget = $this->m->Widgets->getWidget($name);
    return $widget->widget($options);
  }
  
  public function begin($name, $options = array()) {
    $widget = $this->m->Widgets->getWidget($name);
    assume($widget instanceof TraversableWidget, tr('%1 is not a traversable widget', $name));
    $widget->begin($options);
    return $widget;
  }
}