<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Snippets\Snippet;
use Jivoo\View\ViewResponse;
use Jivoo\Core\Lib;
use Jvioo\InvalidArgumentException;

/**
 * A toolkit snippet.
 */
class JtkSnippet extends Snippet {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Jtk');

  /**
   * {@inheritdoc}
   */
  protected $parameters = array('object');
  
  /**
   * @var string Class name for associated settings object.
   */
  protected $objectType = 'Jivoo\Jtk\JtkObject';
  
  /**
   * Get associated JTK settings object.
   * @return JtkObject JTK settings object.
   */
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
      throw new InvalidArgumentException(tr('JTK object is null'));
    $this->viewData['object'] = $object;
    $response = parent::__invoke($parameters);
    if ($response instanceof ViewResponse)
      return $response->body;
    return $response;
  }
}
