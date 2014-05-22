<?php


interface IAcl {
  public function hasPermission(IRecord $user = null, $permission);
}