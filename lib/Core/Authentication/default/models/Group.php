<?php
class Group extends ActiveModel {

  protected $hasMany = array(
    'users' => 'User'
  );

}

class GroupRecord extends ActiveRecord {
  private $permissions;

  private function fetchPermissions() {
    $dataSource = $this->getModel()->otherSources->groups_permissions;
    $result = $dataSource->select()
      ->where('group_id = ?')
      ->addVar($this->id)
      ->execute();
    $this->permissions = array();
    while ($row = $result->fetchAssoc()) {
      $this->permissions[$row['permission']] = true;
    }
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
    $dataSource = $this->getModel()->otherSources->groups_permissions;
    if ($value == true AND !$this->hasPermission($key)) {
      $this->permissions[$key] = true;
      $dataSource->insert()
        ->addPair('group_id', $this->id)
        ->addPair('permission', $key)
        ->execute();
    }
    else if ($value == false AND $this->hasPermission($key)) {
      unset($this->permissions[$key]);
      $dataSource->delete()
        ->where('group_id = ? AND permission = ?')
        ->addVar($this->id)
        ->addVar($key)
        ->execute();
    }
  }
}
