<?php
// Module
// Description    : The PeanutCMS widget system
// Author         : apakoh.dk
// Dependencies   : Core/Database Core/Routing Core/Templates Core/Controllers
//                  Core/Authentication PeanutCMS/Backend Core/Editors
//                  Core/Models Core/Helpers

/**
 * Widget system module
 * @package PeanutCMS\Widgets
 */
class Widgets extends ModuleBase {
  
  private $available = array();
  
  private $areas = array();
  
  protected function init() {
    /** @TODO Temporary work-around. Fix backend stuff! */
    $this->m->Helpers->Widgets->addModule($this);
    
    $this->register(new TextWidget($this->p('templates/text-widget.html.php')));
    $this->register(new TestWidget($this->p('test-widget.html.php')));
    
    $this->m->Routing->onRendering(array($this, 'renderWidgets'));
  }
  
  /**
   * Register a widget
   * @param WidgetBase $widget Widget
   */
  public function register(WidgetBase $widget) {
    $this->available[get_class($widget)] = $widget;
  }
  
  /**
   * Call main-function of all configured widgets
   */
  public function renderWidgets() {
    $areas = $this->config['areas']->getArray();
    foreach ($areas as $area => $widgets) {
      if (!isset($this->areas[$area])) {
        $this->areas[$area] = array();
      }
      foreach ($widgets as $widget) {
        $name = $widget['name'];
        $config = $widget['config'];
        if (!isset($this->available[$name])) {
          // Widget not available.. Remove and inform user
          continue;
        }
        $object = $this->available[$name];
        if (isset($widget['title'])) {
          $title = $widget['title'];
        }
        else {
          $title = $object->getDefaultTitle();
        }
        if ($object->getView()->isDefaultTemplate()) {
          // Find alternate widget template:
          $templateName = Utilities::camelCaseToDashes($name);
          $template = $this->view->findTemplate('widgets/' . $templateName . '.html');
          if ($template !== false) {
            $object->getView()->setTemplate($template);
          }
        }
        $html = $object->main($config);
        if ($html === false) {
          // Widget incorrectly configured.. Remove and inform user
          continue;
        }
        $this->areas[$area][] = array(
          'title' => $title,
          'content' => $html
        );
      }
    }
  }
  
  /**
   * Get widgets for a widget area
   * @param string $area Name of widget area 
   * @return array[] List of widgets of the following array format:
   * 
   * <code>
   * array(
   *   'title' => ..., // Widget title as string, empty for no title
   *   'content' => ..., // Widget content as string
   * )
   * </code>
   */
  public function get($area) {
    if (!isset($this->areas[$area])) {
      return array();
    }
    return $this->areas[$area];
  }
}