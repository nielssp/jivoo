<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View;

/**
 * Module for creating web application user interfaces.
 * @property-read IconMenu $menu A global menu object.
 */
class Administration extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('View', 'Snippets');
  
  /**
   * @var IconMenu Global menu.
   */
  private $menu;

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->menu = new IconMenu(tr('Administration'));
  }

  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'menu':
        return $this->$property;
    }
    return parent::__get($property);
  }
}