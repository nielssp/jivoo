<?php
// Module
// Name           : Widgets
// Description    : The Jivoo widget system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Database Jivoo/Routing Jivoo/Templates Jivoo/Controllers
//                  Jivoo/Authentication Jivoo/Editors
//                  Jivoo/Models Jivoo/Helpers

/**
 * Widget system module
 * @package Jivoo\Widgets
 */
class Widgets extends LoadableModule {
  
  protected $modules = array('View', 'Routing', 'Helpers', 'Models');
  
    /**
   * @var array Associative array of widget names and objects
   */
  private $loaded = array();

  /**
   * @var array Associative array of widget names and objects
   */
  private $available = array();
  
  /**
   * @var array Associative array of widget areas and widget lists
   */
  private $areas = array();
  
  
  private $widgets = array();
  
  protected function init() {
    if (!isset($this->config['areas'])) {
      $this->config['areas'] = array(
        'sidebar' => array(
          array(
            'name' => 'TextWidget',
            'title' => tr('Welcome to ' . $this->app->name),
            'config' => array('text' => tr('Welcome to %1. This is a widget for displaying basic information in the sidebar.', $this->app->name))
          ),
        )
      );
    }
    
    $this->register(new TextWidget(
      $this->app,
      $this->p('templates/text-widget.html.php')
    ));
    
    $this->view->addTemplateDir($this->p('templates'), 4);
    
    $this->m->Routing->attachEventHandler('beforeRender', array($this, 'renderWidgets'));
  }
  
  public function addWidget(Widget $widget) {
    $name = preg_replace('/Widget$/', '', get_class($widget));
    $this->widgets[$name] = $widget;
  }
  
  public function getWidget($name) {
    if (!isset($this->widgets[$name])) {
      $class = $name . 'Widget';
      Lib::assumeSubclassOf($class, 'Widget');
      $this->widgets[$name] = new $class($this->app);
    }
    return $this->widgets[$name];
  }
  
  public function hasWidget($name) {
    return isset($this->widgets[$name]);
  }
  
  /**
   * Register a widget
   * @param WidgetBase $widget Widget
   */
  public function register(Widget $widget) {
    $this->available[get_class($widget)] = $widget;
  }
  
  /**
   * Get a widget instance
   * @param string $name Widget name
   * @return Widget|null A widget object or null on failure
   */
  public function __get($name) {
    return $this->getWidget($name);
  }
  
  public function __isset($name) {
    return $this->hasWidget($name);
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
        if (isset($this->loaded[$name])) {
          $object = $this->loaded[$name];
        }
        else if (isset($this->available[$name])) {
          $object = $this->available[$name];
          $this->loaded[$name] = $object;
        }
        else {
          // Widget not available.. Remove and inform user
          continue;
        }
        if (isset($widget['title'])) {
          $title = $widget['title'];
        }
        else {
          $title = $object->getDefaultTitle();
        }
        if ($object->isDefaultTemplate()) {
          // Find alternate widget template:
          $templateName = Utilities::camelCaseToDashes($name);
          $template = $this->view->findTemplate('widgets/' . $templateName . '.html');
          if ($template !== false) {
            $object->setTemplate($template);
          }
        }
        $html = $object->widget($config);
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