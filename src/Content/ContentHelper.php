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

  protected $helpers = array('Form');
  
  private $editors = array();
  
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

    $this->addEditor(new TextareaEditor('html', function($content) {
      return html_entity_decode($content, null, 'UTF-8');
    }));
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
  
  public function addEditor(Editor $editor) {
    $format = $editor->getFormat();
    $this->editors[$format] = $editor;
  }

  public function getEditor($format) {
    if (isset($this->editors[$format]))
      return $this->editors[$format];
    return null;
  }

  /**
   * Create select-element of available content formats.
   * @param string $field Field name.
   * @return string Select element HTML.
   */
  public function selectFormat($field) {
    $options = array_keys($this->editors);
    $options = array_combine($options, $options);
    return $this->Form->selectOf($field . 'Format', $options);
  }
  
  /**
   * Content filter that replaces "jivoo:*" style links. 
   * @param string $content Input content.
   * @return string Output content.
   */
  public function linkFilter($content) {
    $routing = $this->m->Routing;
    return preg_replace_callback(
      '/\bjivoo:([a-zA-Z0-9_]+:[-a-zA-Z0-9_\.~\\\\:\[\]?&+%\/=]+)/', 
      function ($matches) use($routing) {
        try {
          return $routing->getLink($matches[1]);
        }
        catch (InvalidRouteException $e) {
          return '#invalid-link';
        }
      },
      $content
    );
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
