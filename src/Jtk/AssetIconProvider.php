<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Assets\Assets;

/**
 * An icon provider that provides icons from the assets directory.
 */
class AssetIconProvider implements IconProvider {
  /**
   * @var Assets
   */
  private $assets;
  
  /**
   * @var string
   */
  private $dir;
  
  /**
   * Construct icon provider.
   * @param Assets $assets Assets module.
   * @param string $dir Subdirectory to look for icons in.
   */
  public function __construct(Assets $assets, $dir = 'img') {
    $this->assets = $assets;
    $this->dir = $dir;
  }

  /**
   * {@inheritdoc}
   */
  public function getIcon($icon, $size = 16) {
    $file = $this->assets->getAsset($this->dir . '/' . $icon . '.png');
    return '<img src="' . $file . '" alt="' . $icon . '" width="' . $size .'" height="' . $size .'" />';
  }
}