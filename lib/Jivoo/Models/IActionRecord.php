<?php
/**
 * Contains several links to actions.
 * @package Jivoo\Models
 */
interface IActionRecord {
  /**
   * Get route to a named action.
   * @param string $action Action name.
   * @return array|ILinkable|string|null A route, see {@see Routing}.
   */
  public function action($action);
}