<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

use Jivoo\Helpers\Helper;
use Jivoo\Models\Model;
use Jivoo\Models\BasicRecord;
use Jivoo\Routing\InvalidRouteException;
use Jivoo\Core\Assume;

/**
 * Content helper.
 */
class ContentHelper extends Helper {
  
  private $purifierConfigs = array();
  
  private $formats = array();
  
  /**
   * @var ContentExtensions Collection of content extensions.
   */
  private $extensions;
  
  protected function init() {
    $this->vendor->import('ezyang/htmlpurifier');
    
    $this->extensions = new ContentExtensions();
    
    $this->extensions->inline(
      'link', array('route' => null), 
      array('Jivoo\Content\ContentExtensions', 'linkFunction')
    );
    $this->extensions->block(
      'break', array(),
      array('Jivoo\Content\ContentExtensions', 'breakFunction')
    );
    $this->extensions->block(
      'page', array('name' => null), 
      array('Jivoo\Content\ContentExtensions', 'pageFunction')
    );
    $this->extensions->block(
      'pagebreak', array(), 
      array('Jivoo\Content\ContentExtensions', 'pageBreakFunction')
    );
  }
  
  public function __get($property) {
    switch ($property) {
      case 'extensions':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  public function register(Model $model, $field) {
    $name = $model->getName();
    if (!isset($this->models[$name]))
      $this->purifierConfigs[$name] = array();
    $this->purifierConfigs[$name][$field] = \HTMLPurifier_Config::createDefault();
  }
  
  public function getPurifierConfig(Model $model, $field) {
    $name = $model->getName();
    Assume::hasKey($this->purifierConfigs, $name);
    Assume::hasKey($this->purifierConfigs[$name], $field);
    return $this->purifierConfigs[$name][$field];
  }
  
  public function getFormat($name) {
    if (!isset($this->formats)) {
      if (!class_exists($name))
        return null;
      $this->formats[$name] = new $name();
    }
    return $this->formats[$name];
  }
  
  public function addFormat(Format $format, $name = null) {
    if (!isset($name))
      $name = get_class($format);
  }
}
