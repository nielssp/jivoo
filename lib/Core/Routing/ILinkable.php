<?php
/**
 * An object that can be used in place of a route, see {@see Routing}
 * @package Core\Routing
 */
interface ILinkable {
  /**
   * Get a route
   * @return mixed A route, see {@see Routing}
   */
  public function getRoute();
}
