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
use Jivoo\Models\Record;

/**
 * Content helper.
 * @property-read ContentExtensions $extensions Collection of content extensions.
 */
class ContentHelper extends Helper {
  
  private $purifierConfigs = array();
  
  private $formats = array();
  
  /**
   * @var ContentExtensions
   */
  private $extensions;
  
  protected function init() {
    $this->vendor->import('ezyang/htmlpurifier');
    
    $this->extensions = new ContentExtensions();
    
    $this->extensions->inline(
      'link', array('route' => null), 
      array($this, 'linkFunction')
    );
    $this->extensions->block(
      'break', array(),
      array($this, 'breakFunction')
    );
    $this->extensions->block(
      'page', array('name' => null), 
      array($this, 'pageFunction')
    );
    $this->extensions->block(
      'pagebreak', array(), 
      array($this, 'pageBreakFunction')
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
  }
  
  public function getFormat($name) {
    if (!isset($this->formats[$name])) {
      if (!class_exists($name)) {
        $this->logger->error('Content format not found: {format}', array('format' => $name));
        return null;
      }
      $this->formats[$name] = new $name();
    }
    return $this->formats[$name];
  }
  
  public function addFormat(Format $format, $name = null) {
    if (!isset($name))
      $name = get_class($format);
  }
  
  /**
   * Insert link for route.
   * @param array $params Content extension parameters.
   * @return string Link.
   */
  public function linkFunction($params) {
    try {
      return $this->m->Routing->getLink($params['route']);
    }
    catch (InvalidRouteException $e) {
      return 'invalid link';
    }
  }
  
  /**
   * Create a break between summary and full content.
   * @param array $params Content extension parameters.
   * @return string Break div.
   */
  public function breakFunction($params) {
    return '<div class="break"></div>';
  }
  
  /**
   * Create a page break.
   * @param array $params Content extension parameters.
   * @return string Page break div.
   */
  public function pageBreakFunction($params) {
    return '<div class="page-break"></div>';
  }
  
  /**
   * Name the current content page.
   * @param array $params Content extension parameters.
   * @return string Page name div.
   */
  public function pageFunction($params) {
    if (isset($params['name']))
      return '<div class="page-name" data-name="' . h($params['name']) . '"></div>';
    else
      return '<div class="page-name"></div>';
  }
}
