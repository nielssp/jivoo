<?php
/**
 * A menu item in the PeanutCMS backend
 * @package PeanutCMS\Backend
 */
class BackendMenuItem implements IGroupable, ILinkable {
  private $route = null;
  private $label = '';
  private $group = 0;
  private $permission = 'backend.access';

  public function __construct($label, $route = null, $group = 0, $permission = 'backend.access') {
    $this->label = $label;
    $this->route = $route;
    $this->group = $group;
    $this->permsision = $permission;
  }

  public function __get($property) {
    switch ($property) {
      case 'label':
      case 'group':
      case 'route':
      case 'permission':
        return $this->$property;
    }
  }

  public function __set($property, $value) {
    switch ($property) {
      case 'label':
      case 'group':
      case 'route':
      case 'permission':
        $this->$property = $value;
    }
  }

  public function getGroup() {
    return $this->group;
  }
}
