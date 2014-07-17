<?php
/**
 * 
 * @package Jivoo\Models
 */
interface IActionRecord {
  /**
   * @param string $action
   * @return array|ILinkable|string|null A route, see {@see Routing}
   */
  public function action($action);
}