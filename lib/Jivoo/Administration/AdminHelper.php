<?php
class AdminHelper extends Helper {
  
  protected $modules = array('Assets', 'Templates');
  
  protected $helpers = array('Html', 'Widget');
  
  public function importDefaultTheme() {
    $this->m->Assets->addAssetDir('Administration', 'default/assets');
    $this->view->addTemplateDir($this->p('Administration', 'default/templates'));
  }
  
  public function link($label, $route, $icon = null, $count = null, $attributes = array()) {
    try {
      $url = $this->m->Routing->getLink($route);
      if (!isset($attributes['class']) and $this->m->Routing->isCurrent($route))
        $attributes['class'] = 'current';
      if (!isset($attributes['title']))
        $attributes['title'] = h($label);
      if (isset($icon))
        $icon = '<span class="icon icon-' . $icon . '"></span>';
      else
        $icon = '';
      if (isset($count))
        $count = '<span class="count">' . $count . '</span>';
      else
        $count = '';
      return '<a href="' . h($url) . '"' .
             $this->Html->addAttributes($attributes) . '>' . $icon .
             '<span class="label">' . $label . '</span>' . $count . '</a>';
    }
    catch (InvalidRouteException $e) {
      Logger::logException($e);
      return '<a href="#invalid-route" class="invalid"><span class="label">' .
             $label . '</span></a>';
    }
  }
  
  public function mainMenu() {
    return $this->Widget->widget('Menu', array(
      
    ));
  }
  
}