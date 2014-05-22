<?php
class RecordAcl extends LoadableAcl {
  
  public function hasPermission(IRecord $user = null, $permission) {
    if (!isset($user))
      return false;
    return $user->hasPermission($permission);
  }
  
}