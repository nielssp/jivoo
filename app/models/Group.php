<?php
class Group extends ActiveModel {

  protected $record = 'GroupRecord';

  protected $hasMany = array(
    'users' => 'User'
  );

}

class GroupRecord extends ActiveRecord {
  private $permissions;

  private function fetchPermissions() {
    $permissions = $this->getModel()
      ->getDatabase()
      ->GroupPermission
      ->where('groupId = ?', $this->id)
      ->select('permission');
    $this->permissions = array();
    foreach ($permissions as $record)
      $this->permissions[$record['permission']] = true;
  }

  public function hasPermission($permission) {
    if ($this->isNew()) {
      return false;
    }
    if (!isset($this->permissions)) {
      $this->fetchPermissions();
    }
    return isset($this->permissions[$permission]);
  }

  public function setPermission($permission, $value) {
    if ($this->isNew()) {
      return false;
    }
    if (!isset($this->permissions)) {
      $this->fetchPermissions();
    }
    $source = $this->getModel()->getDatabase()->GroupPermission;
    if ($value == true AND !$this->hasPermission($permission)) {
      $this->permissions[$permission] = true;
      $source->create()
        ->set('groupId', $this->id)
        ->set('permission', $permission)
        ->save();
    }
    else if ($value == false AND $this->hasPermission($permission)) {
      unset($this->permissions[$permission]);
      $source->where('groupId = ?', $this->id)
        ->and('permission = ?', $permission)
        ->delete();
    }
  }
}
