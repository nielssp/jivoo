<?php
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