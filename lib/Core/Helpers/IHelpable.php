<?php
/**
 * An object that accepts helpers
 * @package Core\Helpers
 */
interface IHelpable {
  /**
   * Get list of helpers that this object requires
   * @return string[] List of helper names
   */
  public function getHelperList();
  
  /**
   * Add a helper to the collection of helpers
   * @param Helper $helper Helper object
   */
  public function addHelper($helper);
}