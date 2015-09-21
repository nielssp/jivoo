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

/**
 * Content helper.
 */
class ContentHelper extends Helper {
  
  private $registered = array();
  
  private $formats = array();
  
  /**
   * @var ContentExtensions Collection of content extensions.
   */
  private $extensions;
  
  protected function init() {
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
  
  public function register(Model $model, $field) {
    $name = $model->getName();
    if (!isset($this->models[$name]))
      $this->registered[$name] = array();
    $this->registered[$name][$field] = true;
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
