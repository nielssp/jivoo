<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Snippets\Snippet;
use Jivoo\Core\Lib;

/**
 * A toolkit snippet.
 */
class JtkSnippet extends Snippet {
  protected $helpers = array('Jtk');
  
  protected $parameters = array('object');
  
  protected $objectType = 'Jivoo\Jtk\JtkObject';
  
  public function getObject() {
    $args = func_get_args();
    $ref  = new \ReflectionClass($this->objectType);
    return $ref->newInstanceArgs($args);
  }
  
  /**
   * {@inheritdoc}
   */
  public function __invoke($parameters = array()) {
    $object = null;
    if (isset($parameters['object']))
      $object = $parameters['object'];
    else if (isset($parameters[0]))
      $object = $parameters[0];
    if (isset($object))
      Lib::assumeSubclassOf($object, $this->objectType);
    else
      throw new \Exception(tr('JTK object is null'));
    $this->viewData['object'] = $object;
    return parent::__invoke($parameters);
  }
}