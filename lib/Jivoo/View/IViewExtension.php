<?php
/**
 * A view extension.
 * @package Jivoo\View
 */
interface IViewExtension {
  /**
   * Prepare extension.
   * @return bool Whether or not the extension should be displayed.
   */
  public function prepare();
}