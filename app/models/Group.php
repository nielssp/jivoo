<?php
class Group extends ActiveRecord {

  protected $hasMany = array(
  	'User' => array('thisKey' => 'group_id'),
  );

  private $permissions;

  private function fetchPermissions() {
    $db = self::connection();
    $result = $db->selectQuery('groups_permissions')
      ->where('group_id = ?')
      ->addVar($this->id)
      ->execute();
    $this->permissions = array();
    while ($row = $result->fetchAssoc()) {
      $this->permissions[$row['permission']] = TRUE;
    }
  }

  public function hasPermission($key) {
    if ($this->isNew()) {
      return FALSE;
    }
    if (!isset($this->permissions)) {
      $this->fetchPermissions();
    }
    if (isset($this->permissions['*']))
      return TRUE;
    if (isset($this->permissions[$key]))
      return TRUE;
    $permArr = explode('.', $key);
    if (count($permArr) <= 1) {
      return FALSE;
    }
    else {
      array_pop($permArr);
      $parentKey = implode('.', $permArr);
      return $this->hasPermission($parentKey);
    }
  }

  public function setPermission($key, $value) {
    if ($this->isNew()) {
      return FALSE;
    }
    if (!isset($this->permissions)) {
      $this->fetchPermissions();
    }
    $db = self::connection();
    if ($value == TRUE AND !$this->hasPermission($key)) {
      $this->permissions[$key] = TRUE;
      $db->insertQuery('groups_permissions')
        ->addPair('group_id', $this->id)
        ->addPair('permission', $key)
        ->execute();
    }
    else if ($this->hasPermission($key)) {
      unset($this->permissions[$key]);
      $db->deleteQuery('groups_permissions')
        ->where('group_id = ? AND permission = ?')
        ->addVar($this->id)
        ->addVar($key)
        ->execute();
    }
  }
}
