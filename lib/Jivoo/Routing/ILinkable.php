<?php
/**
 * An object that can be used in place of a route, see {@see Routing}.
 * @package Jivoo\Routing
 */
interface ILinkable {
  /**
   * Get a route.
   * @return string|array|ILinkable|null A route, see {@see Routing}.
   */
  public function getRoute();
}
