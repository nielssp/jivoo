<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

/**
 * An icon provider for a small number of default (Unicode-based) icons. 
 */
class CoreIconProvider extends ClassIconProvider {
  /**
   * {@inheritdoc}
   */
  protected $classPrefix = 'jtk-icon-';
  
  /**
   * {@inheritdoc}
   */
  protected $mapping = array(
    'checkmark' => 'checkmark',
    'ok' => 'checkmark',
    'cancel' => 'close',
    'close' => 'close',
  );
}