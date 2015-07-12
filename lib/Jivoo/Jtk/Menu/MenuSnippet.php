<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Menu;

use Jivoo\Jtk\JtkSnippet;

/**
 * A recursive icon menu snippet.
 */
class MenuSnippet extends JtkSnippet {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Icon', 'Jtk');

  /**
   * {@inheritdoc}
   */
  protected $objectType = 'Jivoo\Jtk\Menu\Menu';
}

