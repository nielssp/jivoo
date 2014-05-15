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

  public function hasPermission($key = null) {
    if ($this->isNew()) {
      return false;
    }
    if (!isset($key)) {
      return true;
    }
    if (!isset($this->permissions)) {
      $this->fetchPermissions();
    }
    if (isset($this->permissions['*']))
      return true;
    if (isset($this->permissions[$key]))
      return true;
    $permArr = explode('.', $key);
    if (count($permArr) <= 1) {
      return false;
    }
    else {
      array_pop($permArr);
      $parentKey = implode('.', $permArr);
      return $this->hasPermission($parentKey);
    }
  }

  public function setPermission($key, $value) {
    if ($this->isNew()) {
      return false;
    }
    if (!isset($this->permissions)) {
      $this->fetchPermissions();
    }
    $source = $this->getModel()->getDatabase()->GroupPermission;
    if ($value == true AND !$this->hasPermission($key)) {
      $this->permissions[$key] = true;
      $source->create()
        ->set('groupId', $this->id)
        ->set('permission', $key)
        ->save();
    }
    else if ($value == false AND $this->hasPermission($key)) {
      unset($this->permissions[$key]);
      $source->where('groupId = ?', $this->id)
        ->and('permission = ?', $key)
        ->delete();
    }
  }
}
