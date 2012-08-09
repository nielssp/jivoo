<?php
class Group extends ActiveRecord {

  protected $hasMany = array(
    'User' => array('thisKey' => 'group_id'),
  );

  private $permissions;

  private function fetchPermissions() {
    $dataSource = self::connection('Group');
    if ($dataSource instanceof ITable) {
      $dataSource = $dataSource->getOwner()->groups_permissions;
      $result = $dataSource->select()
        ->where('group_id = ?')
        ->addVar($this->id)
        ->execute();
      $this->permissions = array();
      while ($row = $result->fetchAssoc()) {
        $this->permissions[$row['permission']] = TRUE;
      }
    }
  }

  public function hasPermission($key = NULL) {
    if ($this->isNew()) {
      return FALSE;
    }
    if (!isset($key)) {
      return TRUE;
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
    $dataSource = self::connection('Group');
    if (!($dataSource instanceof ITable)) {
      return;
    }
    $dataSource = $dataSource->getOwner()->groups_permissions;
    if ($value == TRUE AND !$this->hasPermission($key)) {
      $this->permissions[$key] = TRUE;
      $dataSource->insert()
        ->addPair('group_id', $this->id)
        ->addPair('permission', $key)
        ->execute();
    }
    else if ($value == FALSE AND $this->hasPermission($key)) {
      unset($this->permissions[$key]);
      $dataSource->delete()
        ->where('group_id = ? AND permission = ?')
        ->addVar($this->id)
        ->addVar($key)
        ->execute();
    }
  }
}
