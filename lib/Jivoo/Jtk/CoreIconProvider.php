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
  public function __construct() {
    parent::__construct('jtk-icon-');
  }
  
  /**
   * {@inheritdoc}
   */
  protected $mapping = array(
    'ok' => 'checkmark',
    'cancel' => 'close',
  );
  
  protected $icons = array(
    'checkmark', 'checkmark2', 'close', 'close2', 'star', 'star2',
    'disk', 'folder', 'folder-open', 'file',
    'arrow-up', 'arrow-down', 'arrow-left', 'arrow-right',
    'arrow-up-left', 'arrow-up-right', 'arrow-down-left', 'arrow-down-right'
  );
}