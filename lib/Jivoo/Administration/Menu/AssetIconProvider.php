<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration\Menu;

class AssetIconProvider implements IIconProvider {
  private $assets;
  private $dir;
  
  public function __construct(Assets $assets, $dir = 'img') {
    $this->assets = $assets;
    $this->dir = $dir;
  }
  
  public function getIcon($icon, $size = 16) {
    $file = $this->assets->getAsset($this->dir . '/' . $icon . '.png');
    return '<img src="' . $file . '" alt="' . $icon . '" />';
  }
}