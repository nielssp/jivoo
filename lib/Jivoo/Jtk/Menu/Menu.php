<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Menu;

/**
 * A menu.
 */
class Menu {
  /**
   * Append one or more menu items.
   * @param Menu|MenuAction $item Menu item.
   * @param Menu|MenuAction $items,... Additional items.
   */
  public function append($item) {
    
  }

  /**
   * Prepend one or more menu items.
   * @param Menu|MenuAction $item Menu item.
   * @param Menu|MenuAction $items,... Additional items.
   */
  public function prepend($item) {
    
  }

  /**
   * Insert one or more menu items.
   * @param Menu|MenuAction $item Menu item.
   * @param Menu|MenuAction $items,... Additional items.
   */
  public function insert($offset, $item) {
    
  }
  
  public function getMenu($id) {
    
  }
  
  public function appendMenu($label, $id = null) {
  }

  public function prependMenu($label, $id = null) {
  }

  public function insertMenu($offset, $label, $id = null) {
  }
  
  public function appendSeparator() {
  }
  
  public function prependSeparator() {
  }
  
  public function insertSeparator($offset) {
  }
  
  public function appendAction($label, $route = null, $icon) {
    
  }
  
  public function prependAction($label, $route = null, $icon) {
    
  }
  
  public function insertAction($offset, $label, $route = null, $icon) {
    
  }
}