<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration\Menu;

interface IIconProvider {
  /**
   * 
   * @param string $icon Icon identifier
   * @param integer $size Requested icon size
   * @return string|null HTML for icon, or null if icon not available
   */
  public function getIcon($icon, $size = 16);
}