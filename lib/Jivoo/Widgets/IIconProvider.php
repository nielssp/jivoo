<?php
interface IIconProvider {
  /**
   * 
   * @param string $icon Icon identifier
   * @param integer $size Requested icon size
   * @return string|null HTML for icon, or null if icon not available
   */
  public function getIcon($icon, $size = 16);
}